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
      <div class="chapter-content">
        <div class = "chapterHeader">
          <h1 class="h2">Chương {{ $content->info->stt }} : {{ $content->info->chapterName }} </h1>
        </div>
        <div class = "inner-content">
          {!! $content->data !!}
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