<aside class="col-sm-1 col-md-1 col-lg-2 sidebar" style="width:10%;">
    <ul class="nav nav-sidebar">
        <li><a></a></li>
    </ul>
    <style>
        .dropdown-menu-new {
            width:80%;

            display: block;

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

        <li @if(Route::currentRouteName() == 'admin_number' )class="active" @endif ><a href="{{ url('admin/number') }}"   >账号总览</a></li>
        <li @if(Route::currentRouteName() == 'notice' )class="active" @endif ><a href="{{ url('admin/notice') }}"   >公告管理</a></li>
        <li @if(Route::currentRouteName() == 'daili' )class="active" @endif ><a class="dropdown-toggle-d" id="number"   >代理管理</a>
            <ul class="dropdown-menu-new @if(Route::currentRouteName() == 'daili' ) show @endif "  >
                <li><a href="{{url('admin/daili')}}">基本信息</a></li>
                <li><a href="{{url('admin/recharge_log')}}">充值记录</a></li>
            </ul>
        </li>

    </ul>



</aside>