@extends('publish.masterPage')
@include('publish.time')
@section('title')
{{ $content->info->bookName }} - chương {{ $content->info->stt }} 
@endsection
@section('content')
<div class="row">
    <div class="col-sm-1">
      <div class="well hidden-xs">
        <p>ADS</p>
      </div>
      <div class="well hidden-xs">
        <p>ADS</p>
      </div>
    </div>
    <div class="col-sm-10 chapter-content-col">
      <div class="content">
        <div class = "chapterHeader">
          <h1 class="h2">Chương {{ $content->info->stt }} : {{ $content->info->chapterName }} </h1>
        </div>
        <div class = "inner-content">
          {!! $content->data !!}
        </div>
      </div>
      <div class="chapter-comments">
        <p>&nbsp;<span class="badge">2</span> Comments:</p><br>
        <div class="row" style="margin:0px;">
          <div class="col-sm-2 text-center">
            <img src="https://i.9mobi.vn/cf/images/2015/03/nkk/nhung-hinh-anh-dep-19.jpg" class="img-circle" height="65" width="65" alt="Avatar">
          </div>
          <div class="col-sm-10">
            <h4>Anja <small>Sep 29, 2015, 9:12 PM</small></h4>
            <p>Keep up the GREAT work! I am cheering for you!! Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <br>
          </div>
          <div class="col-sm-2 text-center">
            <img src="http://hinhnendepnhat.net/wp-content/uploads/2017/11/Hinh-anh-dep-girl-xinh-de-thuong-nhat-nam-mau-tuat-2018.jpg" class="img-circle" height="65" width="65" alt="Avatar">
          </div>
          <div class="col-sm-10">
            <h4>John Row <small>Sep 25, 2015, 8:25 PM</small></h4>
            <p>I am so happy for you man! Finally. I am looking forward to read about your trendy life. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <br>  
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-1">
      <div class="well">
        <p>ADS</p>
      </div>
      <div class="well">
        <p>ADS</p>
      </div>
    </div>
</div>
@section('scripts')
<script>
 
</script>
@endsection
@endsection