

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4>Kết Quả Phân Tích Hình Ảnh</h4>
        </div>
        <div class="card-body">

            Mô tả từ Gemini
            <h5 class="text-primary">Mô tả ảnh:</h5>
            <p>{{ $description }}</p>

            Hiển thị số lượng từng loại đối tượng
            <h5 class="mt-3">Số lượng nhận diện:</h5>
            <ul class="list-group">
                <li class="list-group-item"> Cây cảnh: <strong>{{ $plantCount }}</strong></li>
                <li class="list-group-item"> Chậu cây: <strong>{{ $potCount }}</strong></li>
                <li class="list-group-item"> Đá trang trí: <strong>{{ $rockCount }}</strong></li>
            </ul>

            Tổng giá trị dự đoán
            <h5 class="mt-4 text-danger">Tổng giá trị dự đoán: <strong>{{ number_format($totalPrice, 0, ',', '.') }} VND</strong></h5>

            Hiển thị gợi ý sản phẩm dựa trên diện tích sân vườn
            <h5 class="mt-4"> Gợi ý sản phẩm cho diện tích {{ $area }} m²:</h5>
            @if($recommendations->isEmpty())
                <p class="text-muted">Không có sản phẩm phù hợp.</p>
            @else
                <div class="row">
                    @foreach ($recommendations as $product)
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $product->name }}</h6>
                                    <p class="card-text text-danger">{{ number_format($product->price, 0, ',', '.') }} VND</p>
                                    <p class="card-text"><small class="text-muted">Diện tích tối thiểu: {{ $product->min_area }} m²</small></p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>


