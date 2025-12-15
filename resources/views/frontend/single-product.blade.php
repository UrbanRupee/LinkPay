@extends('frontend.layout.design1')

@section('css')
@endsection

@section('content')
    <main class="main-wrapper">
        <!-- Start Shop Area  -->
        <div class="axil-single-product-area axil-section-gap pb--0 bg-color-white">
            <div class="single-product-thumb mb--40">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 mb--40">
                            <div class="row">
                                <div class="col-lg-10 order-lg-2">
                                    <div class="single-product-thumbnail-wrap zoom-gallery">
                                        <div class="single-product-thumbnail product-large-thumbnail-3 axil-product">
                                            @isset($data->image1)
                                                <div class="thumbnail">
                                                    <a href="{{ $data->image1 }}" class="popup-zoom">
                                                        <img src="{{ $data->image1 }}" alt="{{ $data->name }}">
                                                    </a>
                                                </div>
                                            @endisset
                                            @isset($data->image2)
                                                <div class="thumbnail">
                                                    <a href="{{ $data->image2 }}" class="popup-zoom">
                                                        <img src="{{ $data->image2 }}" alt="{{ $data->name }}">
                                                    </a>
                                                </div>
                                            @endisset
                                            @isset($data->image3)
                                                <div class="thumbnail">
                                                    <a href="{{ $data->image3 }}" class="popup-zoom">
                                                        <img src="{{ $data->image3 }}" alt="{{ $data->name }}">
                                                    </a>
                                                </div>
                                            @endisset
                                            @isset($data->image4)
                                                <div class="thumbnail">
                                                    <a href="{{ $data->image4 }}" class="popup-zoom">
                                                        <img src="{{ $data->image4 }}" alt="{{ $data->name }}">
                                                    </a>
                                                </div>
                                            @endisset
                                            @isset($data->image5)
                                                <div class="thumbnail">
                                                    <a href="{{ $data->image5 }}" class="popup-zoom">
                                                        <img src="{{ $data->image5 }}" alt="{{ $data->name }}">
                                                    </a>
                                                </div>
                                            @endisset
                                            @isset($data->image6)
                                                <div class="thumbnail">
                                                    <a href="{{ $data->image6 }}" class="popup-zoom">
                                                        <img src="{{ $data->image6 }}" alt="{{ $data->name }}">
                                                    </a>
                                                </div>
                                            @endisset
                                        </div>
                                        @if ((($data->old_amount - $data->amount) / $data->old_amount) * 100 > 0)
                                            <div class="label-block">
                                                <div class="product-badget">
                                                    {{ number_format((($data->old_amount - $data->amount) / $data->old_amount) * 100, 2) }}%
                                                    OFF</div>
                                            </div>
                                        @endif
                                        <div class="product-quick-view position-view">
                                            <a href="{{ $data->image1 }}" class="popup-zoom">
                                                <i class="far fa-search-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 order-lg-1">
                                    <div class="product-small-thumb-3 small-thumb-wrapper">
                                        @if ($data->image1 != '')
                                            <div class="small-thumb-img">
                                                <img src="{{ $data->image1 }}" alt="{{ $data->name }}">
                                            </div>
                                        @endif
                                        @if ($data->image2 != '')
                                            <div class="small-thumb-img">
                                                <img src="{{ $data->image2 }}" alt="{{ $data->name }}">
                                            </div>
                                        @endif
                                        @if ($data->image3 != '')
                                            <div class="small-thumb-img">
                                                <img src="{{ $data->image3 }}" alt="{{ $data->name }}">
                                            </div>
                                        @endif
                                        @if ($data->image4 != '')
                                            <div class="small-thumb-img">
                                                <img src="{{ $data->image4 }}" alt="{{ $data->name }}">
                                            </div>
                                        @endif
                                        @if ($data->image5 != '')
                                            <div class="small-thumb-img">
                                                <img src="{{ $data->image5 }}" alt="{{ $data->name }}">
                                            </div>
                                        @endif
                                        @if ($data->image6 != '')
                                            <div class="small-thumb-img">
                                                <img src="{{ $data->image6 }}" alt="{{ $data->name }}">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 mb--40">
                            <div class="single-product-content">
                                <div class="inner">
                                    <h2 class="product-title">{{ $data->name }}</h2>
                                    <span class="price-amount">₹{{ $data->amount }}</span>
                                    <span class="price old-price"
                                        style="text-decoration: line-through;">₹{{ $data->amount }}</span>
                                    <ul class="product-meta">
                                        <li><i class="fal fa-check"></i>In stock</li>
                                        <li><i class="fal fa-check"></i>Free delivery available</li>
                                    </ul>
                                    <p class="description">{{ $data->description }}</p>

                                    <div class="product-variations-wrapper">

                                        <!-- Start Product Variation  -->
                                        {{-- <div class="product-variation">
                                            <h6 class="title">Colors:</h6>
                                            <div class="color-variant-wrapper">
                                                <ul class="color-variant">
                                                    <li class="color-extra-01 active"><span><span
                                                                class="color"></span></span>
                                                    </li>
                                                    <li class="color-extra-02"><span><span class="color"></span></span>
                                                    </li>
                                                    <li class="color-extra-03"><span><span class="color"></span></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> --}}
                                        <!-- End Product Variation  -->

                                        <!-- Start Product Variation  -->
                                        {{-- <div class="product-variation product-size-variation">
                                            <h6 class="title">Size:</h6>
                                            <ul class="range-variant">
                                                <li>xs</li>
                                                <li>s</li>
                                                <li>m</li>
                                                <li>l</li>
                                                <li>xl</li>
                                            </ul>
                                        </div> --}}
                                        <!-- End Product Variation  -->

                                    </div>

                                    <!-- Start Product Action Wrapper  -->
                                    <div class="product-action-wrapper d-flex-center">
                                        <!-- Start Quentity Action  -->
                                        {{-- <div class="pro-qty"><input type="text" value="1"></div> --}}
                                        <!-- End Quentity Action  -->

                                        <!-- Start Product Action  -->
                                        <ul class="product-action d-flex-center mb--0">
                                            <li class="add-to-cart"><a href="javascript:void(0)"
                                                    onclick="addtocart('{{ $data->id }}')"
                                                    class="axil-btn btn-bg-primary">Add
                                                    to Cart</a></li>
                                            <li class="wishlist"><a href="javascript:void(0)"
                                                    onclick="addwishlist('{{ $data->id }}')"
                                                    class="axil-btn wishlist-btn"><i class="far fa-heart"></i></a></li>
                                        </ul>
                                        <!-- End Product Action  -->

                                    </div>
                                    <!-- End Product Action Wrapper  -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End .single-product-thumb -->

            <div class="woocommerce-tabs wc-tabs-wrapper bg-vista-white">
                <div class="container">
                    <ul class="nav tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab"
                                aria-controls="description" aria-selected="true">Description</a>
                        </li>
                        {{-- <li class="nav-item " role="presentation">
                            <a id="additional-info-tab" data-bs-toggle="tab" href="#additional-info" role="tab"
                                aria-controls="additional-info" aria-selected="false">Additional Information</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a id="reviews-tab" data-bs-toggle="tab" href="#reviews" role="tab"
                                aria-controls="reviews" aria-selected="false">Reviews</a>
                        </li> --}}
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel"
                            aria-labelledby="description-tab">
                            <div class="product-desc-wrapper">
                                <div class="row">
                                    <div class="col-lg-12 mb--30">
                                        <div class="single-desc">
                                            <h5 class="title">Specifications:</h5>
                                            <p>{{ $data->specification }}</p>
                                        </div>
                                    </div>
                                    <!-- End .col-lg-6 -->
                                    {{-- <div class="col-lg-6 mb--30">
                                        <div class="single-desc">
                                            <h5 class="title">Care & Maintenance:</h5>
                                            <p>Use warm water to describe us as a product team that creates amazing UI/UX
                                                experiences, by crafting top-notch user experience.</p>
                                        </div>
                                    </div> --}}
                                    <!-- End .col-lg-6 -->
                                </div>
                                <!-- End .row -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <ul class="pro-des-features">
                                            <li class="single-features">
                                                <div class="icon">
                                                    <img src="/assets/images/product/product-thumb/icon-3.png"
                                                        alt="icon">
                                                </div>
                                                Easy Returns
                                            </li>
                                            <li class="single-features">
                                                <div class="icon">
                                                    <img src="/assets/images/product/product-thumb/icon-2.png"
                                                        alt="icon">
                                                </div>
                                                Quality Service
                                            </li>
                                            <li class="single-features">
                                                <div class="icon">
                                                    <img src="/assets/images/product/product-thumb/icon-1.png"
                                                        alt="icon">
                                                </div>
                                                Original Product
                                            </li>
                                        </ul>
                                        <!-- End .pro-des-features -->
                                    </div>
                                </div>
                                <!-- End .row -->
                            </div>
                            <!-- End .product-desc-wrapper -->
                        </div>
                        {{-- <div class="tab-pane fade" id="additional-info" role="tabpanel"
                            aria-labelledby="additional-info-tab">
                            <div class="product-additional-info">
                                <div class="table-responsive">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Stand Up</th>
                                                <td>35″L x 24″W x 37-45″H(front to back wheel)</td>
                                            </tr>
                                            <tr>
                                                <th>Folded (w/o wheels) </th>
                                                <td>32.5″L x 18.5″W x 16.5″H</td>
                                            </tr>
                                            <tr>
                                                <th>Folded (w/ wheels) </th>
                                                <td>32.5″L x 24″W x 18.5″H</td>
                                            </tr>
                                            <tr>
                                                <th>Door Pass Through </th>
                                                <td>24</td>
                                            </tr>
                                            <tr>
                                                <th>Frame </th>
                                                <td>Aluminum</td>
                                            </tr>
                                            <tr>
                                                <th>Weight (w/o wheels) </th>
                                                <td>20 LBS</td>
                                            </tr>
                                            <tr>
                                                <th>Weight Capacity </th>
                                                <td>60 LBS</td>
                                            </tr>
                                            <tr>
                                                <th>Width</th>
                                                <td>24″</td>
                                            </tr>
                                            <tr>
                                                <th>Handle height (ground to handle) </th>
                                                <td>37-45″</td>
                                            </tr>
                                            <tr>
                                                <th>Wheels</th>
                                                <td>Aluminum</td>
                                            </tr>
                                            <tr>
                                                <th>Size</th>
                                                <td>S, M, X, XL</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                            <div class="reviews-wrapper">
                                <div class="row">
                                    <div class="col-lg-6 mb--40">
                                        <div class="axil-comment-area pro-desc-commnet-area">
                                            <h5 class="title">01 Review for this product</h5>
                                            <ul class="comment-list">
                                                <!-- Start Single Comment  -->
                                                <li class="comment">
                                                    <div class="comment-body">
                                                        <div class="single-comment">
                                                            <div class="comment-img">
                                                                <img src="/assets/images/blog/author-image-4.png"
                                                                    alt="Author Images">
                                                            </div>
                                                            <div class="comment-inner">
                                                                <h6 class="commenter">
                                                                    <a class="hover-flip-item-wrapper" href="#">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Cameron Williamson">Eleanor
                                                                                Pena</span>
                                                                        </span>
                                                                    </a>
                                                                    <span class="commenter-rating ratiing-four-star">
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i
                                                                                class="fas fa-star empty-rating"></i></a>
                                                                    </span>
                                                                </h6>
                                                                <div class="comment-text">
                                                                    <p>“We’ve created a full-stack structure for our working
                                                                        workflow processes, were from the funny the century
                                                                        initial all the made, have spare to negatives. ”
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <!-- End Single Comment  -->

                                                <!-- Start Single Comment  -->
                                                <li class="comment">
                                                    <div class="comment-body">
                                                        <div class="single-comment">
                                                            <div class="comment-img">
                                                                <img src="/assets/images/blog/author-image-4.png"
                                                                    alt="Author Images">
                                                            </div>
                                                            <div class="comment-inner">
                                                                <h6 class="commenter">
                                                                    <a class="hover-flip-item-wrapper" href="#">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Rahabi Khan">Courtney
                                                                                Henry</span>
                                                                        </span>
                                                                    </a>
                                                                    <span class="commenter-rating ratiing-four-star">
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                    </span>
                                                                </h6>
                                                                <div class="comment-text">
                                                                    <p>“We’ve created a full-stack structure for our working
                                                                        workflow processes, were from the funny the century
                                                                        initial all the made, have spare to negatives. ”</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <!-- End Single Comment  -->

                                                <!-- Start Single Comment  -->
                                                <li class="comment">
                                                    <div class="comment-body">
                                                        <div class="single-comment">
                                                            <div class="comment-img">
                                                                <img src="/assets/images/blog/author-image-5.png"
                                                                    alt="Author Images">
                                                            </div>
                                                            <div class="comment-inner">
                                                                <h6 class="commenter">
                                                                    <a class="hover-flip-item-wrapper" href="#">
                                                                        <span class="hover-flip-item">
                                                                            <span data-text="Rahabi Khan">Devon Lane</span>
                                                                        </span>
                                                                    </a>
                                                                    <span class="commenter-rating ratiing-four-star">
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                        <a href="#"><i class="fas fa-star"></i></a>
                                                                    </span>
                                                                </h6>
                                                                <div class="comment-text">
                                                                    <p>“We’ve created a full-stack structure for our working
                                                                        workflow processes, were from the funny the century
                                                                        initial all the made, have spare to negatives. ”
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <!-- End Single Comment  -->
                                            </ul>
                                        </div>
                                        <!-- End .axil-commnet-area -->
                                    </div>
                                    <!-- End .col -->
                                    <div class="col-lg-6 mb--40">
                                        <!-- Start Comment Respond  -->
                                        <div class="comment-respond pro-des-commend-respond mt--0">
                                            <h5 class="title mb--30">Add a Review</h5>
                                            <p>Your email address will not be published. Required fields are marked *</p>
                                            <div class="rating-wrapper d-flex-center mb--40">
                                                Your Rating <span class="require">*</span>
                                                <div class="reating-inner ml--20">
                                                    <a href="#"><i class="fal fa-star"></i></a>
                                                    <a href="#"><i class="fal fa-star"></i></a>
                                                    <a href="#"><i class="fal fa-star"></i></a>
                                                    <a href="#"><i class="fal fa-star"></i></a>
                                                    <a href="#"><i class="fal fa-star"></i></a>
                                                </div>
                                            </div>

                                            <form action="#">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Other Notes (optional)</label>
                                                            <textarea name="message" placeholder="Your Comment"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label>Name <span class="require">*</span></label>
                                                            <input id="name" type="text">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label>Email <span class="require">*</span> </label>
                                                            <input id="email" type="email">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="form-submit">
                                                            <button type="submit" id="submit"
                                                                class="axil-btn btn-bg-primary w-auto">Submit
                                                                Comment</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- End Comment Respond  -->
                                    </div>
                                    <!-- End .col -->
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
            <!-- woocommerce-tabs -->

        </div>
        <!-- End Shop Area  -->

        <!-- Start Recently Viewed Product Area  -->
        @if (count($similar_products) > 0)
        <div class="axil-product-area bg-color-white axil-section-gap pb--50 pb_sm--30">
            <div class="container">
                <div class="section-title-wrapper">
                    <span class="title-highlighter highlighter-primary"><i class="far fa-shopping-basket"></i> Your
                        Products</span>
                    <h2 class="title">Similar Products</h2>
                </div>
                <div class="recent-product-activation slick-layout-wrapper--15 axil-slick-arrow arrow-top-slide">
                    @foreach ($similar_products as $item)
                        <div class="slick-single-layout">
                            <div class="axil-product">
                                <div class="thumbnail">
                                    <a href="/product/{{$item->id}}">
                                        <img src="{{$item->image1}}" alt="{{$item->name}}">
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
                                        <h5 class="title"><a href="single-product.html">{{$item->name}}</a></h5>
                                        <div class="product-price-variant">
                                            <span class="price old-price">₹{{$item->old_amount}}</span>
                                            <span class="price current-price">₹{{$item->amount}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <!-- End .slick-single-layout -->
                </div>
            </div>
        </div>
        @endif
        <!-- End Recently Viewed Product Area  -->
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
