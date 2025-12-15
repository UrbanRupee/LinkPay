@extends('user.layout.users')
@section('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,700;1,600&family=Indie+Flower&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap');
.bgimg{
  width: 100%;;
  height: 100%;
  background-repeat: no-repeat;
  background-color: rgba(34, 34, 34, 0.075);
  background-size: cover;
  background-blend-mode: overlay;
    /* background-position: 100% center; */
  /* bg image right */
  background-position: top 25% right 0;
  background-image: url(../comm/assets/image/1.jpg);
}

.para{
  font-weight: 500;
  padding: 0.5rem 0;
  word-spacing: 0.2rem;
  letter-spacing: 0.07rem;
  font-size: 1.3rem; 
  line-height: 100%;
  width: 100%;
}


.cards{
  padding: 0rem;
  width: 6.1rem;
  height: 6rem;
  border-radius: 0.3rem;
  position: relative;
  box-shadow: var(--boxShadow2);
  transition: var(--transation);
  cursor: pointer;
  box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5),
  7px 7px 20px 0px rgba(0,0,0,.1),
  4px 4px 5px 0px rgba(0,0,0,.1);
  margin: 0 1rem;
}

.radius_100{border-radius: 100%;}


.person {
  min-width: 100%;
  display: flex;
  justify-content: center;
}

.personTree .ul_ {
  overflow: auto;
  display: flex;
  padding-top: 1rem;
  position: relative;
  transition: all 0.5s;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  overflow: hidden;
   overflow: auto;
   margin: auto;
}

.personTree .ul_ li {
  display: table-cell;
  text-align: center;
  position: relative;
  padding:3.5rem 0;
  transition: all 0.5s;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
}

.personTree .ul_ li::before, 
.personTree .ul_ li::after {
  content: '';
  position: absolute;
  top: 0;
  right: 50%;
  border-top: 0.2rem solid #ccc;
  width: 50%;
  height: 3rem;
}

.personTree .ul_ li::after {
  right: auto;
  left: 50%;
  border-left: 0.2rem solid #ccc;
}

.personTree .ul_ li:only-child::after, 
.personTree .ul_ li:only-child::before { display: none; }


.personTree .ul_ li:first-child::before, 
.personTree .ul_ li:last-child::after { border: 0 none;}

.personTree .ul_ li:last-child::before {
  border-right: 0.2rem solid #ccc;
  border-radius: 0 5px 0 0;
  -webkit-border-radius: 0 5px 0 0;
  -moz-border-radius: 0 5px 0 0;
}

.personTree .ul_ li:first-child::after {
  border-radius: 5px 0 0 0;
  -webkit-border-radius: 5px 0 0 0;
  -moz-border-radius: 5px 0 0 0;
}

.personTree .ul_ ul::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  border-left: 0.2rem solid #ccc;
  width: 0;
  height: 5rem;
}

.personTree .ul_ li .parentBox {
  transition: all 0.5s;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  margin-top: 0.2rem;
}

.personTree .ul_ li .treeFam {position: relative; }



.personTree .cards:hover{box-shadow:rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;}


/* ! 1200px  ----- */
@media(max-width: 1200px) {
    html { font-size: 100%; }
    .cards{
      width: 3rem;
      height: 3rem;
    }
    .para{ font-size: 1rem; }
}
</style>
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ $title }}</h5>
                    </div>
                    <div class="personTree">
        <ul class="ul_">
            <li>
                <div class="treeFam">
                    <div>
                        <div class="person pp ">
                            <div class="name cards bgimg radius_100" style="background-image:url({{$main->image}});"></div>
                        </div>
                        <p class="para">{{$main->name}}</p>
                    </div>
                   
                    <div class="parentBox">               
                        <ul class="ul_">                       
                            <!-- 1 -->
                            @foreach($team as $row)
                            <li>
                                <div class="treeFam" > 
                                    <div class="person child">
                                        <div class="bgimg cards radius_100" id="bgimg_2" style="background-image:url({{$row->image}});"></div>
                                    </div>
                                    <a href="/user/tree-view/{{$row->id}}" class="para">{{$row->name}}</a>
                                </div>
                            </li>
                            @endforeach
                            @for($i=0;$i< (8-count($team));$i++)
                            <li>
                                <div class="treeFam" > 
                                    <div class="person child">
                                        <div class="bgimg cards radius_100" id="bgimg_2"></div>
                                    </div>
                                    <!--<p class="para">Add Member</p>-->
                                    <a class="para" target="_blank" href="{{url('/')}}/register?refferal={{$main->userid}}">Add member</a>
                                </div>
                            </li>
                            @endfor
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
