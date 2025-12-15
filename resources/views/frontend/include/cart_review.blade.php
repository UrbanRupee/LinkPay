<div class="cart-dropdown" id="cart-dropdown">
    <div class="cart-content-wrap">
        <div class="cart-header">
            <h2 class="header-title">Cart review</h2>
            <button class="cart-close sidebar-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-body">
            <ul class="cart-item-list">
                @if (count(usercart(user('userid'))) > 0)
                    @foreach (usercart(user('userid')) as $item)
                        <li class="cart-item" id="cartproduct{{$item->id}}">
                            <div class="item-img">
                                <a href="/product/{{ $item->pid }}"><img src="{{ product($item->pid, 'image1') }}"
                                        alt="Commodo Blown Lamp"></a>
                                <button class="close-btn" onclick="deletecartproduct('{{$item->id}}')"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="item-content">
                                <h3 class="item-title"><a href="/products/{{ $item->pid }}">{{ $item->name }}</a>
                                </h3>
                                <div class="item-price"><span
                                        class="currency-symbol">₹</span>{{ number_format($item->quantity * $item->amount, 2) }}
                                </div>
                                <div class="pro-qty item-quantity">
                                    <input type="number" class="quantity-input" value="{{ $item->quantity }}" readonly>
                                </div>
                            </div>
                        </li>
                    @endforeach
                @else
                    <h2>Cart Empty!!</h2>
                @endif
            </ul>
        </div>
        @if (count(usercart(user('userid'))) > 0)
        <div class="cart-footer">
            <h3 class="cart-subtotal">
                <span class="subtotal-title">Subtotal:</span>
                <span class="subtotal-amount">₹{{ number_format(usercart(user('userid'), 'subtotal')) }}</span>
            </h3>
            <div class="group-btn">
                <a href="/cart" class="axil-btn btn-bg-primary viewcart-btn">View Cart</a>
                {{-- <a href="/checkout" class="axil-btn btn-bg-secondary checkout-btn">Checkout</a> --}}
            </div>
        </div>
        @endif
    </div>
</div>
