<header class='text-bg-dark'>
  <nav class='px-5 py-3 d-flex flex-row justify-content-between'>
    <ul class='d-flex fex-row'>
      <li>
        <a class='text-white' href='/'>HOME</a>
      </li>
      <li>
        <a href='<?=base_url('buyer/list')?>'>거래처</a>
      </li>
      <!-- <li>
        <a href='/shipping'>택배</a>
      </li> -->
      <li>
        <a href='<?=base_url('orders')?>'>주문</a>
      </li>
      <!-- <li>
        <a href='/contactus'>문의내역</a>
      </li> -->
      <li>
        <a href='<?=base_url('product')?>'>상품</a>
      </li>
      <li>
        <a href='<?=base_url('brand')?>'>브랜드</a>
      </li>
      <li>
        <a href='<?=base_url('stock')?>'>재고</a>
      </li>
      <li>
        <a href='<?=base_url('orders/paypalList')?>'>paypal</a>
      </li>
    </ul>
    <div>
      <a class='text-secondary' href="/logout">logout</a>    
    </div>
  </nav>
</header>