

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
            <h3>SẢN PHẨM TÌM THẤY TRONG ẢNH</h3>
            <p>*********** tên cây *************</p>
            <ul class="list-group">
                @foreach($plants as $plant=>$quantity)
                    <li class="list-group-item"> Cây cảnh: <strong>{{ $plant }} có số lượng ({{$quantity}})</strong></li>
                @endforeach
            </ul>
            <p>*********** tên chậu cây *************</p>
            <ul class="list-group">
                @foreach($pots as $pot=>$quantity)
                    <li class="list-group-item"> Cây cảnh: <strong>{{ $pot }} có số lượng ({{$quantity}})</strong></li>
                @endforeach
            </ul>
            <p>*********** loại đá *************</p>
            <ul class="list-group">
                @foreach($rocks as $rock=>$quantity)
                    <li class="list-group-item"> Cây cảnh: <strong>{{ $rock }} có số lượng ({{$quantity}})</strong></li>
                @endforeach
            </ul>

            <h3>CÁC SẢN PHẨM CÓ TRONG CỬA HÀNG</h3>
            <ul class="list-group">
                @foreach($storePlants as $storePlant)
                    <li class="list-group-item">
                         Cây cảnh: <strong>{{ $storePlant->name }} ---- giá {{ number_format($storePlant->price, 0, ',', '.') }} VND</strong>
                         <img src="{{ asset('uploads/' . $storePlant->image) }}" class="card-img-top" alt="{{ $storePlant->name }}" width="100px">
                    </li>
                @endforeach
            </ul>
            <ul class="list-group">
                @foreach($storePots as $storePot)
                    <li class="list-group-item">
                         chậu cây: <strong>{{ $storePot->name }} ---- giá {{ number_format($storePot->price, 0, ',', '.') }} VND</strong>
                         <img src="{{ asset('uploads/' .$storePot->image) }}" class="card-img-top" alt="{{ $storePot->name }}" width="100px">
                    </li>
                @endforeach
            </ul>
            <ul class="list-group">
                @foreach($storeRocks as $storeRock)
                    <li class="list-group-item">
                        Đá: <strong>{{ $storeRock->name }} ---- giá {{ number_format($storeRock->price, 0, ',', '.') }} VND</strong>
                        <img src="{{ asset('uploads/' .$storeRock->image) }}" class="card-img-top" alt="{{ $storeRock->name }}" width="100px">
                    </li>
                @endforeach
            </ul>

            <h3>CÁC SẢN PHẨM THAY THẾ CÁC SẢN PHẨM THIẾU</h3>
            <ul class="list-group">
                @foreach($storePlantsReplace as $PlantsReplaceItem)
                    <li class="list-group-item">
                         Cây cảnh: <strong>{{ $PlantsReplaceItem->name }} ---- giá {{ number_format($PlantsReplaceItem->price, 0, ',', '.') }} VND</strong>
                         <img src="{{ asset('uploads/' . $PlantsReplaceItem->image) }}" class="card-img-top" alt="{{ $PlantsReplaceItem->name }}" width="100px">
                    </li>
                @endforeach
            </ul>
            <ul class="list-group">
                @foreach($storePotsReplace as $PotsReplaceItem)
                    <li class="list-group-item">
                         chậu cây: <strong>{{ $PotsReplaceItem->name }} ---- giá {{ number_format($PotsReplaceItem->price, 0, ',', '.') }} VND</strong>
                         <img src="{{ asset('uploads/' .$PotsReplaceItem->image) }}" class="card-img-top" alt="{{ $PotsReplaceItem->name }}" width="100px">
                    </li>
                @endforeach
            </ul>
            <ul class="list-group">
                @foreach($storeRocksReplace as $RocksReplaceItem)
                    <li class="list-group-item">
                        Đá: <strong>{{ $RocksReplaceItem->name }} ---- giá {{ number_format($RocksReplaceItem->price, 0, ',', '.') }} VND</strong>
                        <img src="{{ asset('uploads/' .$RocksReplaceItem->image) }}" class="card-img-top" alt="{{ $RocksReplaceItem->name }}" width="100px">
                    </li>
                @endforeach
            </ul>

            <h3>Tổng giá trị dự đoán</h3>
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
                                <img src="{{ asset('uploads/' .$product->image) }}" class="card-img-top" alt="{{ $product->name }}" width="100px">
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


