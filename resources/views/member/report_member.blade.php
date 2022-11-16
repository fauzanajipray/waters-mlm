@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $title => backpack_url('member/' . $user->id . '/report-member'),
    'Report' => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
  <div class="container-fluid">
    <h2>
      <span class="text-capitalize">{{$title}}</span>
    </h2>
  </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="" method="get" id="form">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required" >
                        <label>Month - Year</label>
                        <input type="hidden" name="month_year" value="{{ $monthYear->startOfMonth()->format('Y-m-d') }}">
                        <div class="input-group date">
                            <input 
                            class="form-control"
                                data-bs-datepicker="{{json_encode([
                                    'minViewMode' => 'months', 
                                    'maxViewMode' =>  'years',
                                    'startView' => 'months', 
                                    'format' => 'MM yyyy'])}}"
                                type="text"
                                id="monthYear"
                                >
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <span class="la la-calendar"></span>
                                </span>
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group required" >
                        <label>Level</label>
                        <input type="number" name="total_downline_level" class="form-control" value="{{ $totalDownlineLevel }}" id="levels">
                    </div>
                </div>
            </div>
        </form>
    </div>
    {{-- <div class="col">
        
        <input type="submit" class="btn btn-primary form-control" value="Submit">
        
    </div> --}}
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <div class="chart-container" style="width: 100%;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after_styles')
    <link rel="stylesheet" href="{{ asset('packages/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}">
@endpush

@push('after_scripts')
    {{-- MONTH YEAR PICKER --}}    
    <script src="{{ asset('packages/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        if(jQuery.ui){var e=$.fn.datepicker.noConflict();$.fn.bootstrapDP=e}else $.fn.bootstrapDP=$.fn.datepicker;var dateFormat=function(){var e=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,t=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,a=/[^-+\dA-Z]/g,d=function(e,t){for(e=String(e),t=t||2;e.length<t;)e="0"+e;return e};return function(n,r,m){var y=dateFormat;if(1!=arguments.length||"[object String]"!=Object.prototype.toString.call(n)||/\d/.test(n)||(r=n,n=void 0),n=n?new Date(n):new Date,isNaN(n))throw SyntaxError("invalid date");"UTC:"==(r=String(y.masks[r]||r||y.masks.default)).slice(0,4)&&(r=r.slice(4),m=!0);var o=m?"getUTC":"get",i=n[o+"Date"](),s=n[o+"Day"](),l=n[o+"Month"](),u=n[o+"FullYear"](),c=n[o+"Hours"](),p=n[o+"Minutes"](),h=n[o+"Seconds"](),M=n[o+"Milliseconds"](),_=m?0:n.getTimezoneOffset(),f={d:i,dd:d(i),ddd:y.i18n.dayNames[s],dddd:y.i18n.dayNames[s+7],m:l+1,mm:d(l+1),mmm:y.i18n.monthNames[l],mmmm:y.i18n.monthNames[l+12],yy:String(u).slice(2),yyyy:u,h:c%12||12,hh:d(c%12||12),H:c,HH:d(c),M:p,MM:d(p),s:h,ss:d(h),l:d(M,3),L:d(M>99?Math.round(M/10):M),t:c<12?"a":"p",tt:c<12?"am":"pm",T:c<12?"A":"P",TT:c<12?"AM":"PM",Z:m?"UTC":(String(n).match(t)||[""]).pop().replace(a,""),o:(_>0?"-":"+")+d(100*Math.floor(Math.abs(_)/60)+Math.abs(_)%60,4),S:["th","st","nd","rd"][i%10>3?0:(i%100-i%10!=10)*i%10]};return r.replace(e,function(e){return e in f?f[e]:e.slice(1,e.length-1)})}}();function bpFieldInitDatePickerElement(e){var t=e,a=t.closest(".input-group").parent().find('input[type="hidden"]'),d=$.extend({format:"dd M yyyy",autoclose:!0},t.data("bs-datepicker"));$picker=t.bootstrapDP(d);var n=a.val();if(n&&n.length){var r=n.split("-"),m=r[0],y=r[1]-1,o=r[2];preparedDate=new Date(m,y,o),t.val(preparedDate),$picker.bootstrapDP("update",preparedDate)}var i=a.val();$picker.on("show hide change",function(e){if(e.date)var n=e.format("yyyy-mm-dd");else try{var n=t.val();"dd/mm/yyyy"===d.format&&(n=new Date(n.split("/")[2],n.split("/")[1]-1,n.split("/")[0]).format("yyyy-mm-dd"))}catch(r){t.val()&&new Noty({type:"error",text:"<strong>Whoops!</strong><br>Sorry we did not recognise that date format, please make sure it uses a yyyy mm dd combination"}).show()}a.val(n);var m=n;"hide"==e.type&&i!=m&&(i=m,monthlySales())})}dateFormat.masks={default:"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"},dateFormat.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]},Date.prototype.format=function(e,t){return dateFormat(this,e,t)},bpFieldInitDatePickerElement($("#monthYear"));
    </script>
    <script>
        // every time montYear change submit form
        (function(){
            $('#monthYear').change(function(){
                $('#form').submit();
            });
            $('#levels').change(function(){
                $('#form').submit();
            });
        })();
    </script>
    
    {{-- CHART --}}
    <script src="{{asset('packages/d3/d3.v7.min.js')}}"></script>
    <script src="{{asset('packages/d3/d3.org.chart.js')}}"></script>
    <script src="{{asset('packages/d3/d3.flextree.2.1.2.js')}}"></script>
    <script>
        var chart;
        var dataFlattened = {!! $dataMember !!};
        chart = new d3.OrgChart()
        .container('.chart-container')
        .data(dataFlattened)
        .nodeHeight((d) => {
            return d.data.height;
        })
        .nodeWidth((d) => {
        return 250;
        })
        .childrenMargin((d) => 50)
        .compactMarginBetween((d) => 25)
        .compactMarginPair((d) => 50)
        .neightbourMargin((a, b) => 25)
        .siblingsMargin((d) => 25)
        .buttonContent(({ node, state }) => {
        return `<div style="px;color:#716E7B;border-radius:5px;padding:4px;font-size:10px;margin:auto auto;background-color:white;border: 1px solid #E4E2E9"> <span style="font-size:9px">${
            node.children
            ? `<i class="fas fa-angle-up"></i>`
            : `<i class="fas fa-angle-down"></i>`
        }</span> ${node.data._directSubordinates}  </div>`;
        })
        .linkUpdate(function (d, i, arr) {
        d3.select(this)
            .attr('stroke', (d) =>
            d.data._upToTheRootHighlighted ? '#152785' : '#E4E2E9'
            )
            .attr('stroke-width', (d) =>
            d.data._upToTheRootHighlighted ? 5 : 1
            );

        if (d.data._upToTheRootHighlighted) {
            d3.select(this).raise();
        }
        })
        .nodeContent(function (d, i, arr, state) {
        const color = '#FFFFFF';
        return `
        <div style="font-family: 'Inter', sans-serif;background-color:${color}; position:absolute;margin-top:-1px; margin-left:-1px;width:${d.width}px;height:${d.height}px;border-radius:10px;border: 1px solid #E4E2E9">
            <div style="background-color:${color};position:absolute;margin-top:-25px;margin-left:${15}px;border-radius:100px;width:50px;height:50px;" ></div>
            <img src=" ${
            d.data.imageUrl
            }" style="position:absolute;margin-top:-20px;margin-left:${20}px;border-radius:100px;width:40px;height:40px;" />
            
            <div style="color:#08011E;position:absolute;right:20px;top:17px;font-size:10px;" class="preview-user" data-url="${d.data.url}"><i class="las la-eye"></i> Preview</div>

            <div style="font-size:15px;color:#08011E;margin-left:20px;margin-top:32px"> ${
            d.data.name
            } </div>
            <div style="color:#08011E;margin-left:20px;margin-top:3px;font-size:10px;"> Level : <b>${
            d.data.level
            } </b></div>
            <div style="color:#08011E;margin-left:20px;margin-top:8px;"> ${
            d.data.contents
            } </div>
        </div>`;
        })
        .render().expandAll();

        $('div.chart-container').on('click', 'div.preview-user', function(){
            window.location.href = $(this).attr('data-url');
        });
  </script>     
@endpush