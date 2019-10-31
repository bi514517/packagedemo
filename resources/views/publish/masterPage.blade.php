<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title','Truyện là lá la - Trang chủ')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="{{ asset('css/style.css')}}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    @yield('header')
</head>
@yield('style')
<body>

    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">
                    <img src="http://www.oogazone.com/wp-content/uploads/2018/05/unique-open-book-file-free.jpg">
                </a>
            </div>
            <form class="navbar-form navbar-right" action="/tim-kiem/" role="search" itemprop="potentialAction" itemscope="" method="get">
                <div class="input-group search-holder">
                    <meta itemprop="target" content="">
                    <input aria-label="Từ khóa tìm kiếm" role="search key" class="form-control" id="search-input" type="search" name="tukhoa" placeholder="Tìm kiếm..." value="" itemprop="query-input" required="">
                    <div class="input-group-btn">
                        <button class="btn btn-default" type="submit" aria-label="Tìm kiếm" role="search">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </div>
                </div>

                <div class="list-group list-search-res hide">
                    <a href="https://truyenfull.vn/vu-dong-can-khon/" class="list-group-item" title="Vũ Động Càn Khôn">Vũ Động Càn Khôn</a>
                    <a href="https://truyenfull.vn/dai-chua-te/" class="list-group-item" title="Đại Chúa Tể">Đại Chúa Tể</a>
                    <a href="https://truyenfull.vn/dai-ma-dau/" class="list-group-item" title="Đại Ma Đầu">Đại Ma Đầu</a>
                    <a href="https://truyenfull.vn/dau-la-dai-luc/" class="list-group-item" title="Đấu La Đại Lục">Đấu La Đại Lục</a>
                    <a href="https://truyenfull.vn/dau-la-dai-luc-2/" class="list-group-item" title="Đấu La Đại Lục II (Tuyệt Thế Đường Môn)">Đấu La Đại Lục II (Tuyệt Thế Đường Môn)</a>
                    <a href="https://truyenfull.vn/tim-kiem/?tukhoa=Đấu" class="list-group-item" title="Xem thêm kết quả khác">
                        <i>Xem thêm kết quả khác <span class="glyphicon glyphicon-search"></span></i>
                    </a>
                </div>
            </form>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="control nav navbar-nav ">
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="glyphicon glyphicon-list"></span> Danh sách <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="https://truyenfull.vn/danh-sach/truyen-moi/" title="Truyện mới cập nhật">Truyện mới cập nhật</a>
                            </li>
                            <li>
                                <a href="https://truyenfull.vn/danh-sach/truyen-hot/" title="Truyện Hot">Truyện Hot</a>
                            </li>
                            <li>
                                <a href="https://truyenfull.vn/danh-sach/truyen-full/" title="Truyện Full">Truyện Full</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid text-center">
        @yield('content')
    </div>

    <footer class="container-fluid text-center">
        @yield('foot_content')
    </footer>

</body>
@yield('scripts')
</html>