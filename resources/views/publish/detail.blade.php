@extends('publish.masterPage')
@include('publish.time')
@section('title')
{{$book->bookName}}
@endsection
@section('style')
    <style>
      table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
      }

      td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }

      tr:nth-child(even) {
        background-color: #dddddd;
      }
      span.label.label-danger,span.label.label-success,span.label.label-primary{
        line-height: 20px;
      }
      #chapters-table {
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
      }

      #chapters-table td, #chapters-table th {
        border: 1px solid #ddd;
        padding: 8px;
      }

      #chapters-table tr:nth-child(even){background-color: #f2f2f2;}

      #chapters-table tr:hover {background-color: #ddd;}

      #chapters-table th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #4CAF50;
        color: white;
      }
    </style>
@endsection
@section('content')
<div class="row content">
    <div class="col-sm-3 sidenav">
      <div class="detail-image-container">
        <img src="{{$book->bookAvatar}}" alt="{{$book->bookName}}" class="avatar-chi-tiet" itemprop="image">
      </div>
      <div class="well hidden-xs">
        <p>ADS</p>
      </div>
      <div class="well hidden-xs">
        <p>ADS</p>
      </div>
    </div>

    <div class="col-sm-7">
    <h4><small>{{timeAgo($book->datePublication)}} bởi {{$book->submitUserName}}<small></h4>
      <hr>
      <h1 class="h2">{{$book->bookName}}</h1>
      <h5>{{$book->authorName}}</h5>
      <h4 class="h5">
        @foreach ($book->categories as $category)
          <a href="{{url('/the-loai/'.$category->id)}}" style="text-decoration: none;">
          @if($category->accept==1)
          <span class="label label-danger">{{$category->name}}</span>
          @else
          <span class="label label-success">{{$category->name}}</span>
          @endif
          </a> 
        @endforeach
        @foreach ($book->tags as $tag)
          <a href="{{url('/the-loai/'.$tag->id)}}">
          <span class="label label-primary">{{$tag->name}}</span>
          </a> 
        @endforeach
      </h4>
      <br>
      <div id="description-container">
        {!!$book->description!!}
      </div>
      <br><br>
      @isset($book->chapters)
      <h4><small>Danh sách chương</small></h4>
      <hr>
      
      <table id="chapters-table">
        <tr>
          <th>Chương số</th>
          <th>Tên chương</th>
          <th>cập nhật</th>
        </tr>
        
          @foreach($book->chapters as $chapter)
          <tr>
            <td>{{$chapter->stt}}</td>
            <td onclick="window.location.href = '{{url('doc-truyen/' . $book->bookId . '/chuong-' . $chapter->stt . '.html')}}';">
              {{$chapter->name}}
            </td>
            <td title="{{timeAgo($chapter->timeUpload)}}">{{$chapter->timeUpload}}</td>
          </tr>
          @endforeach
       

      </table>
      <div style="text-align:center;">
        <ul class="pagination">
          @foreach($book->pagination as $pagination)
          <li @if($book->page == $pagination->value) class="active" @endif >
            <a onclick="{{$pagination->script}}">{{$pagination->value}}</a>
          </li>
          @endforeach
        </ul>
      </div>
       @endisset
      <hr>

      <h4>Leave a Comment:</h4>
      <form role="form">
        <div class="form-group">
          <textarea class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Submit</button>
      </form>
      <br><br>
      
      <p><span class="badge">2</span> Comments:</p><br>
      
      <div class="row">
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
    <div class="col-sm-2 sidenav">
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
  function showmore() {
    var dots = document.getElementById("dots");
    var moreText = document.getElementById("more");
    var btnText = document.getElementById("myBtn");

    if (dots.style.display === "none") {
      dots.style.display = "inline";
      btnText.innerHTML = "Read more"; 
      moreText.style.display = "none";
    } else {
      dots.style.display = "none";
      btnText.innerHTML = "Read less"; 
      moreText.style.display = "inline";
    }
  }
  function goToPage(){
    var chapt = prompt('Mời nhập số trang ', '');
    if (chapt != null) {
      var xInt = parseInt(chapt);
      window.location.href = '?page='+xInt;
    }
  }
</script>
@endsection
@endsection