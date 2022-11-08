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
    <div class="col-md-6">
        <div class="form-group required">
            <label>Month - Year</label>
            <input type="hidden" name="month_year" value="{{Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')}}">
            <div class="input-group date">
                <input 
                class="form-control"
                    data-bs-datepicker="{{json_encode([
                        'minViewMode' => 'months', 
                        'maxViewMode' =>  'years',
                        'startView' => 'months', 
                        'format' => 'M yyyy'])}}"
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
    <script src="{{ asset('packages/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        if (jQuery.ui) {
            var datepicker = $.fn.datepicker.noConflict();
            $.fn.bootstrapDP = datepicker;
        } else {
            $.fn.bootstrapDP = $.fn.datepicker;
        }

        var dateFormat=function(){var a=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,b=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,c=/[^-+\dA-Z]/g,d=function(a,b){for(a=String(a),b=b||2;a.length<b;)a="0"+a;return a};return function(e,f,g){var h=dateFormat;if(1!=arguments.length||"[object String]"!=Object.prototype.toString.call(e)||/\d/.test(e)||(f=e,e=void 0),e=e?new Date(e):new Date,isNaN(e))throw SyntaxError("invalid date");f=String(h.masks[f]||f||h.masks.default),"UTC:"==f.slice(0,4)&&(f=f.slice(4),g=!0);var i=g?"getUTC":"get",j=e[i+"Date"](),k=e[i+"Day"](),l=e[i+"Month"](),m=e[i+"FullYear"](),n=e[i+"Hours"](),o=e[i+"Minutes"](),p=e[i+"Seconds"](),q=e[i+"Milliseconds"](),r=g?0:e.getTimezoneOffset(),s={d:j,dd:d(j),ddd:h.i18n.dayNames[k],dddd:h.i18n.dayNames[k+7],m:l+1,mm:d(l+1),mmm:h.i18n.monthNames[l],mmmm:h.i18n.monthNames[l+12],yy:String(m).slice(2),yyyy:m,h:n%12||12,hh:d(n%12||12),H:n,HH:d(n),M:o,MM:d(o),s:p,ss:d(p),l:d(q,3),L:d(q>99?Math.round(q/10):q),t:n<12?"a":"p",tt:n<12?"am":"pm",T:n<12?"A":"P",TT:n<12?"AM":"PM",Z:g?"UTC":(String(e).match(b)||[""]).pop().replace(c,""),o:(r>0?"-":"+")+d(100*Math.floor(Math.abs(r)/60)+Math.abs(r)%60,4),S:["th","st","nd","rd"][j%10>3?0:(j%100-j%10!=10)*j%10]};return f.replace(a,function(a){return a in s?s[a]:a.slice(1,a.length-1)})}}();dateFormat.masks={default:"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"},dateFormat.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]},Date.prototype.format=function(a,b){return dateFormat(this,a,b)};

        function bpFieldInitDatePickerElement(element) {
            var $fake = element,
                $field = $fake.closest('.input-group').parent().find('input[type="hidden"]'),
            $customConfig = $.extend({
                format: 'dd M yyyy',
                autoclose: true,
            }, $fake.data('bs-datepicker'));
            $picker = $fake.bootstrapDP($customConfig);

            var $existingVal = $field.val();
            if( $existingVal && $existingVal.length ){
                // Passing an ISO-8601 date string (YYYY-MM-DD) to the Date constructor results in
                // varying behavior across browsers. Splitting and passing in parts of the date
                // manually gives us more defined behavior.
                // See https://stackoverflow.com/questions/2587345/why-does-date-parse-give-incorrect-results
                var parts = $existingVal.split('-');
                var year = parts[0];
                var month = parts[1] - 1; // Date constructor expects a zero-indexed month
                var day = parts[2];
                preparedDate = new Date(year, month, day);
                $fake.val(preparedDate);
                $picker.bootstrapDP('update', preparedDate);
            }

            // prevent users from typing their own date
            // since the js plugin does not support it
            // $fake.on('keydown', function(e){
            //     e.preventDefault();
            //     return false;
            // });

            var prevVal = $field.val();
            $picker.on('show hide change', function(e){
                if( e.date ){
                    var sqlDate = e.format('yyyy-mm-dd');
                } else {
                    try {
                        var sqlDate = $fake.val();

                        if( $customConfig.format === 'dd/mm/yyyy' ){
                            sqlDate = new Date(sqlDate.split('/')[2], sqlDate.split('/')[1] - 1, sqlDate.split('/')[0]).format('yyyy-mm-dd');
                        }
                    } catch(e){
                        if( $fake.val() ){
                            new Noty({
                                type: "error",
                                text: "<strong>Whoops!</strong><br>Sorry we did not recognise that date format, please make sure it uses a yyyy mm dd combination"
                                }).show();
                        }
                    }
                }
                $field.val(sqlDate);
                var newVal = sqlDate;
                if(e.type == 'hide' && prevVal != newVal){
                  prevVal = newVal;
                  monthlySales();
                }
            });
        }
        bpFieldInitDatePickerElement($('#monthYear'));
    </script>
    <script src="{{asset('packages/d3/d3.v7.min.js')}}"></script>
    <script src="{{asset('packages/d3/d3.org.chart.js')}}"></script>
    <script src="{{asset('packages/d3/d3.flextree.2.1.2.js')}}"></script>
    <script>
        var chart;
        var dataFlattened = [
            {
                id: 1,
                name: 'Kevin D',
                level: 'L3',
                imageUrl: "{{backpack_url('images/profile.jpg')}}",
                url: "{{backpack_url('member/1/show')}}",
                parentId: "",
                height: 175,
                _directSubordinates:2,
                _totalSubordinates:4, 
                contents: `<p class="mb-0">Total Bonus : <b>Rp 11,000,000</b></p>
                <p class="mb-0">Total Omset : <b>Rp 200,000,000</b></p>
                <p class="mb-0">B. Omset : <b>Rp 2,500,000</b></p>
                <p class="mb-0">B. Pribadi : <b>Rp 4,000,0000</b></p>
                <p class="mb-0">B. Sponsor : <b>Rp 3,000,0000</b></p>
                <p class="mb-0">Overriding : <b>Rp 1,500,0000</b></p>`
            }, 
            {
                id: 2,
                name: 'Budi',
                level: 'L2',
                imageUrl: "{{backpack_url('images/profile.jpg')}}",
                url: "{{backpack_url('member/1/show')}}",
                parentId: 1,
                height: 175,
                _directSubordinates:1,
                _totalSubordinates:1, 
                contents: `<p class="mb-0">Total Bonus : <b>Rp 4,000,000</b></p>
                <p class="mb-0">Total Omset : <b>Rp 50,000,000</b></p>
                <p class="mb-0">B. Omset : <b>Rp -</b></p>
                <p class="mb-0">B. Pribadi : <b>Rp 2,000,0000</b></p>
                <p class="mb-0">B. Sponsor : <b>Rp 1,500,0000</b></p>
                <p class="mb-0">Overriding : <b>Rp 1,000,0000</b></p>`
            }, 
            {
                id: 3,
                name: 'Andi',
                level: 'L2',
                imageUrl: "{{backpack_url('images/profile.jpg')}}",
                url: "{{backpack_url('member/1/show')}}",
                parentId: 1,
                height: 175,
                _directSubordinates:1,
                _totalSubordinates:1, 
                contents: `<p class="mb-0">Total Bonus : <b>Rp 4,000,000</b></p>
                <p class="mb-0">Total Omset : <b>Rp 50,000,000</b></p>
                <p class="mb-0">B. Omset : <b>Rp -</b></p>
                <p class="mb-0">B. Pribadi : <b>Rp 2,000,0000</b></p>
                <p class="mb-0">B. Sponsor : <b>Rp 1,500,0000</b></p>
                <p class="mb-0">Overriding : <b>Rp 1,000,0000</b></p>`
            }, 
            {
                id: 4,
                name: 'Tono',
                level: 'L2',
                imageUrl: "{{backpack_url('images/profile.jpg')}}",
                url: "{{backpack_url('member/1/show')}}",
                parentId: 3,
                height: 175,
                _directSubordinates:0,
                _totalSubordinates:0, 
                contents: `<p class="mb-0">Total Bonus : <b>Rp 2,000,000</b></p>
                <p class="mb-0">Total Omset : <b>Rp 25,000,000</b></p>
                <p class="mb-0">B. Omset : <b>Rp -</b></p>
                <p class="mb-0">B. Pribadi : <b>Rp 1,000,0000</b></p>
                <p class="mb-0">B. Sponsor : <b>Rp 1,000,0000</b></p>
                <p class="mb-0">Overriding : <b>Rp 7,000,0000</b></p>`
            }, 
            {
                id: 5,
                name: 'Joko',
                level: 'L1',
                imageUrl: "{{backpack_url('images/profile.jpg')}}",
                url: "{{backpack_url('member/1/show')}}",
                parentId: 2,
                height: 175,
                _directSubordinates:0,
                _totalSubordinates:0, 
                contents: `<p class="mb-0">Total Bonus : <b>Rp 2,000,000</b></p>
                <p class="mb-0">Total Omset : <b>Rp 25,000,000</b></p>
                <p class="mb-0">B. Omset : <b>Rp -</b></b></p>
                <p class="mb-0">B. Pribadi : <b>Rp 1,000,0000</b></p>
                <p class="mb-0">B. Sponsor : <b>Rp 1,000,0000</b></p>
                <p class="mb-0">Overriding : <b>Rp 7,000,0000</b></p>`
            }, 
        ];
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