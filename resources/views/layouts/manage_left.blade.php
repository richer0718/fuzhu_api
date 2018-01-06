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
        @if(session('is_kefu') == session('username'))
        <li @if(Route::currentRouteName() == 'kefu' )class="active" @endif ><a href="{{ url('manage/kefu') }}"   >二级账号</a></li>
        @endif
        <li @if(Route::currentRouteName() == 'number_guaji' )class="active" @endif ><a href="{{ url('manage/number') }}"   >挂机账号</a></li>
        <li @if(Route::currentRouteName() == 'number_history' )class="active" @endif ><a>历史账号</a>
            <ul class="dropdown-menu-new  show "  >
                <li><a href="{{url('manage/number/1')}}">完成订单</a></li>
                <li><a href="{{url('manage/number/3')}}">问题订单</a></li>
            </ul>
        </li>
        <li @if(Route::currentRouteName() == 'number_long' ) class="active" @endif ><a href="{{ url('manage/number/2') }}"   >长期账号</a></li>
        <li @if(Route::currentRouteName() == 'manage_notice' )class="active" @endif ><a href="{{ url('manage/manage_notice') }}"   >公告列表</a></li>
        <li @if(Route::currentRouteName() == 'number_log' )class="active" @endif ><a href="{{ url('manage/log') }}"   >日志</a></li>


    </ul>



</aside>