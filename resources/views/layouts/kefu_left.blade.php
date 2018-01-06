<aside class="col-sm-1 col-md-1 col-lg-2 sidebar" style="width:10%;">
    <ul class="nav nav-sidebar">
        <li><a></a></li>
    </ul>
    <style>
        .dropdown-menu-new {
            width:100%;

            display: none;

            min-width: 160px;
            padding: 5px 0;
            margin: 2px 0 0;
            font-size: 14px;
            text-align: left;
            list-style: none;
            background-color: #fff;

            background-clip: padding-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .dropdown-menu-new>li>a {
            display: block;
            padding: 3px 20px;
            clear: both;
            font-weight: 400;
            line-height: 1.42857143;
            color: #333;
            white-space: nowrap;
        }
        .show{
            display:block;
        }

    </style>
    <script>
        $(function(){
            $('.dropdown-toggle-d').click(function(){
                if($(this).parent().find('.dropdown-menu-new').hasClass('show')){
                    $(this).parent().find('.dropdown-menu-new').removeClass('show');

                }else{
                    $('.dropdown-menu-new').removeClass('show');
                    $(this).parent().find('.dropdown-menu-new').addClass('show');

                }

            })
        })
    </script>
    <ul class="nav nav-sidebar">
        @if(session('kefupower')['power1'])
            <li @if(Route::currentRouteName() == 'number' )class="active" @endif ><a href="{{ url('kefu/number') }}"   >新增账号</a></li>
        @endif
        @if(session('kefupower')['power2'])
            <li @if(Route::currentRouteName() == 'search' )class="active" @endif ><a  href="{{url('kefu/searchOrder')}}"  >账号查询</a></li>
        @endif
        @if(session('kefupower')['power3'])
            <li @if(Route::currentRouteName() == 'wancheng_order' )class="active" @endif ><a   href="{{url('kefu/number/1')}}"   >完成订单</a></li>
        @endif
        @if(session('kefupower')['power4'])
                <li @if(Route::currentRouteName() == 'wenti_order' )class="active" @endif ><a   href="{{url('kefu/number/3')}}" >问题订单</a></li>
        @endif
    </ul>



</aside>