@extends('frontend.layout.design1')

@section('css')
@endsection

@section('content')
    <main class="main-wrapper">
        <!-- Start Breadcrumb Area  -->
        <div class="axil-breadcrumb-area">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="inner">
                            <ul class="axil-breadcrumb">
                                <li class="axil-breadcrumb-item"><a href="index.html">Home</a></li>
                                <li class="separator"></li>
                                <li class="axil-breadcrumb-item active" aria-current="page">My Account</li>
                            </ul>
                            <h1 class="title">Explore All Products</h1>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-4">
                        <div class="inner">
                            <div class="bradcrumb-thumb">
                                <img src="assets/images/product/product-45.png" alt="Image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumb Area  -->
        <!-- Start Shop Area  -->
        <div class="axil-shop-area axil-section-gap bg-color-white">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="axil-shop-top">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="category-select">
                                        <!-- Start Single Select  -->
                                        <select class="single-select">
                                            <option value="">Select Category</option>
                                            @foreach ($category as $item)
                                                <option value="{{ $item->id }}">{{ ucfirst($item->name) }}</option>
                                            @endforeach
                                        </select>
                                        <!-- End Single Select  -->

                                        <!-- Start Single Select  -->
                                        <select class="single-select">
                                            <option>Price Range</option>
                                            <option>0 - 100</option>
                                            <option>100 - 500</option>
                                            <option>500 - 1000</option>
                                            <option>1000 - 1500</option>
                                        </select>
                                        <!-- End Single Select  -->

                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="category-select mt_md--10 mt_sm--10 justify-content-lg-end">
                                        <!-- Start Single Select  -->
                                        <select class="single-select">
                                            <option>Sort by Latest</option>
                                            <option>Sort by Name</option>
                                            <option>Sort by Price</option>
                                        </select>
                                        <!-- End Single Select  -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row row--15">
                    @foreach ($products as $item)
                        <div class="col-xl-3 col-lg-4 col-sm-6">
                            <div class="axil-product product-style-one has-color-pick mt--40">
                                <div class="thumbnail">
                                    <a href="/product/{{ $item->id }}">
                                        <img src="{{ $item->image1 }}" alt="{{ $item->name }}">
                                    </a>
                                    @if ((($item->old_amount - $item->amount) / $item->old_amount) * 100 > 0)
                                        <div class="label-block label-right">
                                            <div class="product-badget">
                                                {{ number_format((($item->old_amount - $item->amount) / $item->old_amount) * 100, 2) }}%
                                                Off
                                            </div>
                                        </div>
                                    @endif
                                    <div class="product-hover-action">
                                        <ul class="cart-action">
                                            <li class="wishlist" onclick="addwishlist('{{ $item->id }}')"><a
                                                    href="javascript:void(0)"><i class="far fa-heart"></i></a>
                                            </li>
                                            <li class="select-option"><a href="javascript:void(0)"
                                                    onclick="addtocart('{{ $item->id }}')">Add to Cart</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="product-content">
                                    <div class="inner">
                                        <h5 class="title"><a href="/product/{{$item->id}}">{{$item->name}}</a></h5>
                                        <div class="product-price-variant">
                                            <span class="price current-price">₹{{$item->amount}}</span>
                                            <span class="price old-price">₹{{$item->old_amount}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- End .container -->
            </div>
            <!-- End Shop Area  -->
            <!-- Start Axil Newsletter Area  -->
            <div class="axil-newsletter-area axil-section-gap pt--0">
                <div class="container">
                    <div class="etrade-newsletter-wrapper bg_image bg_image--5">
                        <div class="newsletter-content">
                            <span class="title-highlighter highlighter-primary2"><i
                                    class="fas fa-envelope-open"></i>Newsletter</span>
                            <h2 class="title mb--40 mb_sm--30">Get weekly update</h2>
                            <div class="input-group newsletter-form">
                                <div class="position-relative newsletter-inner mb--15">
                                    <input placeholder="example@gmail.com" type="text">
                                </div>
                                <button type="submit" class="axil-btn mb--15">Subscribe</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End .container -->
            </div>
            <!-- End Axil Newsletter Area  -->
    </main>
@endsection

@section('js')
@endsection
