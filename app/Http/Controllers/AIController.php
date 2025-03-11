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
        // Đếm số lượng cây cảnh, chậu cây, đá trang trí
        $plantCount = substr_count($description, 'cây cảnh') + substr_count($description, 'plant');
        $potCount = substr_count($description, 'chậu cây') + substr_count($description, 'pot');
        $rockCount = substr_count($description, 'đá trang trí') + substr_count($description, 'rock');

        // Truy vấn bảng products để lấy giá trung bình
        $plantPrice = Product::where('name', 'LIKE', '%cây cảnh%')->avg('price') ?? 0;
        $potPrice = Product::where('name', 'LIKE', '%chậu cây%')->avg('price') ?? 0;
        $rockPrice = Product::where('name', 'LIKE', '%đá trang trí%')->avg('price') ?? 0;

        // Tính tổng giá trị
        $totalPrice = ($plantCount * $plantPrice) + ($potCount * $potPrice) + ($rockCount * $rockPrice);



        preg_match_all('/(\d+)\s*(cây\s+[^\d]+)/u', $description, $matches);

        $plantList = [];
        if (!empty($matches[0])) {
            foreach ($matches[1] as $index => $quantity) {
                $name = trim($matches[2][$index]); // Tên cây (ví dụ: "cây bonsai", "cây trầu bà")
                if (isset($plantList[$name])) {
                    $plantList[$name] += (int)$quantity; // Cộng dồn số lượng nếu đã có
                } else {
                    $plantList[$name] = (int)$quantity;
                }
            }
        }
        // dd($plantList);

        // Gợi ý sản phẩm dựa trên diện tích sân vườn
        $recommendations = Product::where('min_area', '<=', $request->area)
            ->orderBy('price', 'desc')
            ->limit(5)
            ->get();

        return view('result', [
            'description' => $description,
            'plantCount' => $plantCount,
            'potCount' => $potCount,
            'rockCount' => $rockCount,
            'totalPrice' => $totalPrice,
            'area' => $request->area,
            'recommendations' => $recommendations
        ]);
    }

   
}

