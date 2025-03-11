<form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" required>
    <input type="number" name="area" placeholder="Nhập diện tích (m²)" required>
    <button type="submit">Phân Tích</button>
</form>




@if ($success = Session('success'))
<div class="alert alert-success text-center" role="alert">
    <strong>{{ $success }}</strong>
</div>
@endif
{{-- alert fail --}}
@if ($fail = Session('error'))
<div class="alert alert-danger text-center" role="alert">
    <strong>{{ $fail }}</strong>
</div>
@endif

{{-- alert error --}}
@if ($errors->any())
<div class="alert alert-danger  text-center" role="alert">
    <ul class="list-group">
        @foreach ($errors->all() as $e)
            <li  class="list-group-item  list-danger">
                {{$e}}
            </li>
        @endforeach
    </ul>
</div>
@endif
