@extends('frontend.layout.design')

@section('css')
@endsection

@section('content')
<main class="main-wrapper">

    <!-- Start Wishlist Area  -->
    <div class="axil-wishlist-area axil-section-gap">
        <div class="container">
            <div class="product-table-heading">
                <h4 class="title">My Wish List on {{setting('app_name')}}</h4>
            </div>
            <div class="table-responsive">
                <table class="table axil-product-table axil-wishlist-table">
                    <thead>
                        <tr>
                            <th scope="col" class="product-remove"></th>
                            <th scope="col" class="product-thumbnail">Product</th>
                            <th scope="col" class="product-title"></th>
                            <th scope="col" class="product-price">Unit Price</th>
                            <th scope="col" class="product-stock-status">Stock Status</th>
                            <th scope="col" class="product-add-cart"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($data) > 0)
                        @foreach ($data as $item)
                        <tr id="row{{$item->id}}">
                            <td class="product-remove"><a href="javascript:void(0)" class="remove-wishlist" onclick="deletewishlistproduct('{{$item->id}}')"><i class="fal fa-times"></i></a></td>
                            <td class="product-thumbnail"><a href="/product/{{$item->pid}}"><img src="{{$item->image}}" alt="{{$item->name}}"></a></td>
                            <td class="product-title"><a href="/product/{{$item->pid}}">{{$item->name}}</a></td>
                            <td class="product-price" data-title="Price"><span class="currency-symbol">₹</span>{{number_format($item->amount)}}</td>
                            <td class="product-stock-status" data-title="Status">{{product($item->pid,'stocks') > 0 ? 'In stocks' : 'Out of stocks'}}</td>
                            <td class="product-add-cart"><a href="javascript:void(0)" onclick="addtocart('{{$item->pid}}')" class="axil-btn btn-outline">Add to Cart</a></td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="6" class="text-center"> Empty Wishlist!</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End Wishlist Area  -->
</main>
@endsection

@section('js')
@endsection
