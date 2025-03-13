<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class AIController extends Controller
{
    public function showFormPicture(){

        return view('formPicture');
    }

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function analyzeImage(Request $request)
    {
        try {
            // Xác thực input
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,jfif|max:2048',
                'area' => 'required|numeric|min:1',
            ]);

            // Đọc file ảnh & mã hóa Base64
            $imagePath = $request->file('image')->getRealPath();
            $imageData = base64_encode(file_get_contents($imagePath));

            // Gửi ảnh đến Sightengine API để kiểm tra nội dung phản cảm
            // $sightengineResponse = Http::attach(
            //     'media', file_get_contents($imagePath), $request->file('image')->getClientOriginalName()
            // )->post('https://api.sightengine.com/1.0/check.json', [
            //     'models' => 'nudity,wad,offensive',
            //     'api_user' => env('SIGHTENGINE_USER'),
            //     'api_secret' => env('SIGHTENGINE_SECRET'),
            // ]);

            // $sightengineResult = $sightengineResponse->json();

            // Debug toàn bộ response API để xem có key 'nudity' không
            // dd($sightengineResult);

            //  Kiểm tra nếu ảnh có nội dung phản cảm
            // if ($sightengineResult['nudity']['safe'] < 0.85 || $sightengineResult['offensive']['prob'] > 0.5) {
            //     return back()->with('error', 'Ảnh chứa nội dung không phù hợp, vui lòng chọn ảnh khác.');
            // }

            // Gửi request đến Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key={$this->apiKey}", [
                'contents' => [
                    'parts' => [
                        ['text' => "Hãy phân tích hình ảnh này và xác định các loại cây cảnh, chậu cây, đá trang trí có trong ảnh.
                            - Trả về kết quả dưới dạng JSON gồm:
                            - 'plants': Danh sách cây cảnh và số lượng mỗi loại.
                            - 'pots': Danh sách chậu cây và số lượng mỗi loại.
                            - 'rocks': Danh sách loại đá trang trí và số lượng mỗi loại.
                            -trả về dạng json giống vậy ```json { plants: { cây phát tài: 1, cây đa búp đỏ: 1, cây sung: 1, cây trầu bà vàng: 1, cây lưỡi hổ: 1, cây trúc mây: 1, cây lan ý: 1, cây đuôi phụng:1 }, pots: { chậu nhựa trắng hình trụ bo tròn: 8 }, rocks: {} } ```
                            Ví dụ:
                            {
                            'plants': {'cây bonsai': 2, 'cây trầu bà': 3},
                            'pots': {'chậu đất nung': 2, 'chậu gốm': 1},
                            'rocks': {'đá cuội': 5, 'đá trắng': 2}
                            }
                        "],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ]);

            // Kiểm tra response từ API
            $results = $response->json();
            // dd($results);
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi gọi API: ' . $e->getMessage());
        }

        if (!isset($results['candidates'][0]['content']['parts'][0]['text'])) {
            return back()->with('error', 'API không nhận diện được hình ảnh.');
        }

        // Lấy mô tả từ Gemini
        $description = strtolower($results['candidates'][0]['content']['parts'][0]['text']);
        // dd($description);
        // kiểm tra giá trị trả về trong json và lấy giá trị trong ngoặc {}
        preg_match('/\{.*\}/s', $description, $matches);
        $jsonString = $matches[0] ?? null;

        if (!$jsonString) {
            // return back()->with('error', 'Không tìm thấy JSON hợp lệ trong kết quả.');
            return back()->with('error', 'xin lỗi không thể phân tích hình ảnh của bạn.');
        }

        $data = json_decode($jsonString, true);
        // dd($data);
        if (!$data) {
            return back()->with('error', 'Lỗi giải mã JSON từ Gemini.');
        }

        // **********************************************************************
        // ***************LẤY CÁC SẢN PHẨM TRONG CỬA HÀNG HIỆN CÓ****************
        // **********************************************************************
        // Lấy danh sách từng loại sản phẩm
        $plants = $data['plants'] ?? [];
        $pots = $data['pots'] ?? [];
        $rocks = $data['rocks'] ?? [];
        // Loại bỏ phần tên khoa học trong ngoặc
        $cleanedPlants = [];
        foreach ($plants as $name => $count) {
            $cleanedName = preg_replace('/\s*\(.*?\)/', '', $name);
            $cleanedPlants[$cleanedName] = $count;
        }
        // dd($plants);
        // Đếm tổng số lượng từng loại
        $plantCount = array_sum($plants);
        $potCount = array_sum($pots);
        $rockCount = array_sum($rocks);

        // Truy vấn giá trung bình từ bảng products
        $plantNames =array_keys($plants);
        $potNames = array_keys($pots);
        $rockNames = array_keys($rocks);
        $plantTotal = 0;
        $potTotal = 0;
        $rockTotal = 0;
        // tổng số lượng sản phẩm không có trong cửa hàng kiểm tra và để tạo ra số lượng sản phẩm thay thế
        $plantMissing = 0;
        $potMissing = 0;
        $rockMissing = 0;
        $plantCategory = 'Cây cảnh';
        $potCategory = 'chậu cây';
        $rockCategory = 'đá';
        // Tính tổng giá trị cây cảnh
        // **** TRONG ĐỒ ÁN CẦN KIỂM TRA SỐ LƯỢNG API TRẢ VỀ KIỂM TRA VỚI SỐ LƯỢNG SẢN PHẨM TRONG KHO
        // NẾU ĐỦ SỐ LƯỢNG CUNG CẤP THÌ BỎ QUA, NẾU THIẾU THÌ CỘNG VÀO MISSING ****
        //  note: cần cập nhật thêm cây đó có số lượng là bao nhiêu <=============
        foreach ($plants as $plant => $quantity) {
            $productPlant = Product::where('name', 'LIKE', "%$plant%")->first();
            if(!$productPlant) {
                $plantMissing += $quantity;
            } else {
                // tìm category của thể loại đó
                $plantCategory = $productPlant->category ?? 'Cây cảnh';
                $plantTotal += $productPlant->price * $quantity;
            }
        }
        // Tính tổng giá trị chậu cây
        foreach ($pots as $pot => $quantity) {
            $productPot = Product::where('name', 'LIKE', "%$pot%")->first();
            if(!$productPot) {
                $potMissing += $quantity;
            } else {
                // tìm category của thể loại đó
                $potCategory = $productPot->category ?? 'chậu cây';
                $potTotal += $productPot->price * $quantity;
            }
        }
        // Tính tổng giá trị đá trang trí
        foreach ($rocks as $rock => $quantity) {
            $productRock = Product::where('name', 'LIKE', "%$rock%")->first();
            if(!$productRock) {
                $rockMissing += $quantity;
            } else {
                // tìm category của thể loại đó
                $rockCategory = $productRock->category ?? 'đá';
                $rockTotal += $productRock->price * $quantity;
            }
        }

        // **********************************************************************
        // *************TÌM KIẾM SẢN PHẨM THAY THẾ TRONG CỬA HÀNG****************
        // **********************************************************************
        // tạo ra sản phẩm thay thế với nhưng sản phẩm không tìm thấy trong api trả về
        $storePlantsReplace  = [];
        $storePotsReplace  = [];
        $storeRocksReplace  = [];
        // Mảng lưu tên sản phẩm đã được chọn để tránh trùng lặp
        $addedPlantNames = [];
        $addedPotNames = [];
        $addedRockNames = [];

        if ($plantMissing >0) {
                for ($i = 1; $i <=$plantMissing; $i++ ) {
                    // Lấy một sản phẩm bất kỳ cùng category
                    $replacementPlant = Product::where('category', $plantCategory)->inRandomOrder()->first();
                    // tính giá tiền
                    $price = $replacementPlant->price;
                    $plantTotal += $price;
                    // Nếu tìm thấy sản phẩm thay thế
                    if ($replacementPlant) {

                        if ($i ==1 ) {
                            // Lưu sản phẩm đầu tiên vào danh sách
                            $storePlantsReplace[] = [
                                'product' =>  $replacementPlant,
                                'quantity' => 1,
                            ];
                            $addedPlantNames[] = $replacementPlant->name;
                        }

                        // Kiểm tra nếu sản phẩm chưa có trong danh sách thì thêm vào
                        if ($i >= 2) {
                            if (!in_array($replacementPlant->name, $addedPlantNames)) {
                                $storePlantsReplace[] = [
                                    'product' =>  $replacementPlant,
                                    'quantity' => 1,
                                ];
                                $addedPlantNames[] =  $replacementPlant->name;
                            } else {
                                // Nếu sản phẩm đã có trong danh sách, tăng số lượng
                                foreach ($storePlantsReplace as &$item) {
                                    if ($item['product']->name == $replacementPlant->name) {
                                        $item['quantity'] += 1;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        if ($potMissing >0) {
            for ($i = 1; $i <=$potMissing; $i++ ) {
                // Lấy một sản phẩm bất kỳ cùng category
                $replacementPot = Product::where('category', $potCategory)->inRandomOrder()->first();
                // tính giá tiền
                $price = $replacementPot->price;
                $potTotal += $price;
                // Nếu tìm thấy sản phẩm thay thế
                if ($replacementPot) {
                    if ($i ==1 ) {
                        // Lưu sản phẩm đầu tiên vào danh sách
                        $storePotsReplace[] = [
                            'product' =>  $replacementPot,
                            'quantity' => 1,
                        ];
                        $addedPotNames[] = $replacementPot->name;
                    }
                    // // Lưu sản phẩm thay thế vào danh sách
                    if ($i >= 2) {
                        if (!in_array($replacementPot->name, $addedPotNames)) {
                            $storePotsReplace[] = [
                                'product' =>  $replacementPot,
                                'quantity' => 1,
                            ];
                            $addedPotNames[] = $replacementPot->name;
                        }else {
                            // Nếu sản phẩm đã có trong danh sách, tăng số lượng
                            foreach ($storePotsReplace as &$item) {
                                if ($item['product']->name == $replacementPot->name) {
                                    $item['quantity'] += 1;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($rockMissing >0) {
            for ($i = 1; $i <=$rockMissing; $i++ ) {
                // Lấy một sản phẩm bất kỳ cùng category
                $replacementRock = Product::where('category', $rockCategory)->inRandomOrder()->first();
                // tính giá tiền
                $price = $replacementRock->price;
                $rockTotal += $price;
                // Nếu tìm thấy sản phẩm thay thế
                if ($replacementRock) {
                    if ($i ==1 ) {
                        // Lưu sản phẩm đầu tiên vào danh sách
                        $storeRocksReplace[] = [
                            'product' =>  $replacementRock,
                            'quantity' => 1,
                        ];
                        $addedRockNames[] = $replacementRock->name;
                    }
                    // // Lưu sản phẩm thay thế vào danh sách
                    if ($i >=2 ) {
                        if (!in_array($replacementRock->name, $addedRockNames)) {
                            $storeRocksReplace[] = [
                                'product' =>  $replacementRock,
                                'quantity' => 1,
                            ];
                            $addedRockNames[] = $replacementRock->name;
                        }else {
                            // Nếu sản phẩm đã có trong danh sách, tăng số lượng
                            foreach ($storeRocksReplace as &$item) {
                                if ($item['product']->name == $replacementRock->name) {
                                    $item['quantity'] += 1;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Tổng giá trị tất cả sản phẩm
        $totalPrice = $plantTotal + $potTotal + $rockTotal;

        // Gom nhóm theo loại sản phẩm có trong cửa hàng
        $storePlants = [];
        $storePots = [];
        $storeRocks = [];
        $matchedProducts = Product::where(function ($query) use ($plants, $pots, $rocks) {
            foreach (array_keys($plants) as $plant) {
                $query->orWhere('name', 'LIKE', "%$plant%");
            }
            foreach (array_keys($pots) as $pot) {
                $query->orWhere('name', 'LIKE', "%$pot%");
            }
            foreach (array_keys($rocks) as $rock) {
                $query->orWhere('name', 'LIKE', "%$rock%");
            }
        })->get();

        foreach ($matchedProducts as $product) {
            if (preg_match('/cây|plant|bonsai/i', $product->name)) {
                $storePlants[] = $product;
            } elseif (preg_match('/chậu|pot|vase/i', $product->name)) {
                $storePots[] = $product;
            } elseif (preg_match('/đá|rock|sỏi/i', $product->name)) {
                $storeRocks[] = $product;
            }
        }

        // Gợi ý sản phẩm dựa trên diện tích sân vườn
        $recommendations = Product::where('min_area', '<=', $request->area)
            ->orderBy('price', 'desc')
            ->limit(8)
            ->get();

        return view('result', [
            'description' => $description,

            'plants' => $plants,
            'pots' => $pots,
            'rocks' => $rocks,

            'plantCount' => $plantCount,
            'potCount' => $potCount,
            'rockCount' => $rockCount,

            'totalPrice' => $totalPrice,

            'storePlants' => $storePlants,
            'storePots' => $storePots,
            'storeRocks' => $storeRocks,

            'storePlantsReplace' => $storePlantsReplace,
            'storePotsReplace' => $storePotsReplace,
            'storeRocksReplace' => $storeRocksReplace,

            'area' => $request->area,

            'recommendations' => $recommendations
        ]);
    }

}

