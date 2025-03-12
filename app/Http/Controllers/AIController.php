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
        // dd($data);
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
        // Tính tổng giá trị cây cảnh
        foreach ($plants as $plant => $quantity) {
            $price = Product::where('name', 'LIKE', "%$plant%")->value('price') ?? 0;
            $plantTotal += $price * $quantity;
        }
        // Tính tổng giá trị chậu cây
        foreach ($pots as $pot => $quantity) {
            $price = Product::where('name', 'LIKE', "%$pot%")->value('price') ?? 0;
            $potTotal += $price * $quantity;
        }
        // Tính tổng giá trị đá trang trí
        foreach ($rocks as $rock => $quantity) {
            $price = Product::where('name', 'LIKE', "%$rock%")->value('price') ?? 0;
            $rockTotal += $price * $quantity;
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
            ->limit(5)
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
            'area' => $request->area,
            'recommendations' => $recommendations
        ]);
    }

    // public function analyzeImage(Request $request)
    // {
    //     try {
    //         // Xác thực input
    //         $request->validate([
    //             'image' => 'required|image|mimes:jpeg,png,jpg,jfif|max:2048',
    //             'area' => 'required|numeric|min:1',
    //         ]);

    //         // Đọc file ảnh & mã hóa Base64
    //         $imagePath = $request->file('image')->getRealPath();
    //         $imageData = base64_encode(file_get_contents($imagePath));

    //         // Gửi ảnh đến Sightengine API để kiểm tra nội dung phản cảm
    //         $sightengineResponse = Http::attach(
    //             'media', file_get_contents($imagePath), $request->file('image')->getClientOriginalName()
    //         )->post('https://api.sightengine.com/1.0/check.json', [
    //             'models' => 'nudity,wad,offensive',
    //             'api_user' => env('SIGHTENGINE_USER'),
    //             'api_secret' => env('SIGHTENGINE_SECRET'),
    //         ]);

    //         $sightengineResult = $sightengineResponse->json();

    //         // Kiểm tra nếu ảnh có nội dung phản cảm
    //         if ($sightengineResult['nudity']['safe'] < 0.85 || $sightengineResult['offensive']['prob'] > 0.5) {
    //             return back()->with('error', 'Ảnh chứa nội dung không phù hợp, vui lòng chọn ảnh khác.');
    //         }

    //         // Gửi request đến Gemini API
    //         $response = Http::withHeaders([
    //             'Content-Type' => 'application/json',
    //         ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key={$this->apiKey}", [
    //             'contents' => [
    //                 'parts' => [
    //                     ['text' => "Hãy phân tích hình ảnh này và xác định các loại cây cảnh, chậu cây, đá trang trí có trong ảnh.
    //                         - Trả về kết quả dưới dạng JSON gồm:
    //                         {
    //                         'plants': {'cây bonsai': 2, 'cây trầu bà': 3},
    //                         'pots': {'chậu đất nung': 2, 'chậu gốm': 1},
    //                         'rocks': {'đá cuội': 5, 'đá trắng': 2}
    //                         }
    //                     "],
    //                     [
    //                         'inlineData' => [
    //                             'mimeType' => 'image/jpeg',
    //                             'data' => $imageData
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ]);

    //         // Kiểm tra response từ API
    //         $results = $response->json();

    //         if (!isset($results['candidates'][0]['content']['parts'][0]['text'])) {
    //             return back()->with('error', 'API không nhận diện được hình ảnh.');
    //         }

    //         // Lấy dữ liệu JSON từ response
    //         $jsonText = $results['candidates'][0]['content']['parts'][0]['text'];
    //         $data = json_decode($jsonText, true);

    //         if (!$data) {
    //             return back()->with('error', 'Dữ liệu từ API không hợp lệ.');
    //         }

    //         // Lấy danh sách từng loại sản phẩm
    //         $plants = $data['plants'] ?? [];
    //         $pots = $data['pots'] ?? [];
    //         $rocks = $data['rocks'] ?? [];

    //         // Đếm tổng số lượng từng loại
    //         $plantCount = array_sum($plants);
    //         $potCount = array_sum($pots);
    //         $rockCount = array_sum($rocks);
    //         dd($plants);
    //         // Truy vấn giá trung bình từ bảng products
    //         $plantPrice = Product::whereIn('name', array_keys($plants))->avg('price') ?? 0;
    //         $potPrice = Product::whereIn('name', array_keys($pots))->avg('price') ?? 0;
    //         $rockPrice = Product::whereIn('name', array_keys($rocks))->avg('price') ?? 0;

    //         // Tính tổng giá trị
    //         $totalPrice = ($plantCount * $plantPrice) + ($potCount * $potPrice) + ($rockCount * $rockPrice);

    //         // Gợi ý sản phẩm dựa trên diện tích sân vườn
    //         $recommendations = Product::where('min_area', '<=', $request->area)
    //             ->orderBy('price', 'desc')
    //             ->limit(5)
    //             ->get();

    //         return view('result', [
    //             'plants' => $plants,
    //             'pots' => $pots,
    //             'rocks' => $rocks,
    //             'plantCount' => $plantCount,
    //             'potCount' => $potCount,
    //             'rockCount' => $rockCount,
    //             'totalPrice' => $totalPrice,
    //             'area' => $request->area,
    //             'recommendations' => $recommendations
    //         ]);
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Lỗi khi gọi API: ' . $e->getMessage());
    //     }
    // }

}

