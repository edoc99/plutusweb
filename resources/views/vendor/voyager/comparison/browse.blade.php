@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->display_name_plural)

<?php
    $dataset = [];
    $itemList = [];
?>

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        </h1>

        @include('voyager::multilingual.language-selector')
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">

                      <!-- Search Box -->

                      <div class="container">
                        <div class="row">
                          <div class="form-group">
                            <form action="" method="GET">
                              <input type="text" class="form-control form-control-lg" id="search" name="name" placeholder="Search for ...." autocomplete="off"/>
                              <input type="submit" class="btn btn-primary" name="submit" value="Submit">
                            </form>
                          </div>
                          <div id="match-list">
                            <!-- Suggestion of the items -->
                          </div>
                        </div>
                      </div>


                          @foreach($dataTypeContent as $data)

                                <!-- Extracting all the Unique Items name and id -->

                                <?php
                                    $jsonlist = $data->items;
                                    $items_array = json_decode($jsonlist, TRUE);
                                     if($items_array != null) {
                                       foreach($items_array as $item) {
                                          if (!in_array($item['name'], array_column($itemList, '0')))
                                          {
                                              $itemName = [];
                                              array_push($itemName, $item['name'], $item['id']);
                                              array_push($itemList, $itemName);
                                          }
                                          //print_r($itemList);
                                       }
                                     }
                                 ?>

                                        @foreach($dataType->browseRows as $row)
                                            @php
                                            if ($data->{$row->field.'_browse'}) {
                                                $data->{$row->field} = $data->{$row->field.'_browse'};
                                            }
                                            @endphp
                                                @if($row->type == 'text')
                                                <!--Required changes-->
                                                    @if($row->display_name == 'Inventory' || $row->display_name == 'Order Details' || $row->display_name == 'Order Details Sup' || $row->display_name == 'Invoice Data' || $row->display_name == 'Items')
                                                        @if(json_decode($data->{$row->field}) == null)
                                                          <!-- null -->
                                                        @else
                                                           <!--Taking JsonData and storing them accordingly-->
                                                            <!-- <?php

                                                              $jsonData = $data->{$row->field};
                                                              $array = new RecursiveIteratorIterator(
                                                              new RecursiveArrayIterator(json_decode($jsonData, TRUE)),
                                                              RecursiveIteratorIterator::SELF_FIRST);

                                                            ?> -->

                                                        <!-- done -->

                                                    @endif
                                                @endif
                                                <!--uptill-->
                                                @else
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <!-- <span>{{ $data->{$row->field} }}</span> -->
                                                @endif
                                        @endforeach
                                    @endforeach

                            <!-- Function for extracting item info -->
                            <?php

                              function loop($dataTypeContent, $dataType, $name) {
                                $dataset = [];
                                foreach ($dataTypeContent as $data) {
                                  foreach ($dataType->browseRows as $row) {
                                    if($row->type == 'text') {
                                      if($row->display_name == 'Inventory' || $row->display_name == 'Order Details' || $row->display_name == 'Order Details Sup' || $row->display_name == 'Invoice Data' || $row->display_name == 'Items')
                                        if(json_decode($data->{$row->field}) == null) {

                                        } else {

                                            $jsonData = $data->{$row->field};
                                            $a = json_decode($jsonData, TRUE);
                                            foreach($a as $item) {

                                               $uses = $item;
                                               $each_item = [];
                                               if($uses['name'] == $name)
                                               {
                                                  array_push($each_item, $data->sup_id, $uses['name'], $uses['price_1'], $uses['price_2'], $uses['price_3'], $uses['price_4']);
                                                  array_push($dataset, $each_item);
                                               }
                                            }
                                        }
                                      }
                                    }
                                  }
                                  return $dataset;
                                }

                            ?>

                            <!-- Sending Get request to store show user response related data on the page  -->

                            <?php

                              if (isset($_GET['submit'])) {
                                $name = $_GET['name'];
                                $dataset = loop($dataTypeContent, $dataType, $name);
                              } else {
                                $name = 'Matki';
                                $dataset = loop($dataTypeContent, $dataType, $name);
                                //echo $_GET['name'];
                            }
                            ?>

                        <!-- price navigation -->
                        <div class="text-center">
                          <h3>{{ $name }}</h3>
                          <nav aria-label="Price navigation" id="price-nav">
                            <ul class="pagination justify-content-center">
                              <li class="page-item"><a class="page-link" data-target="1" >Price 1</a></li>
                              <li class="page-item"><a class="page-link" data-target="2" >Price 2</a></li>
                              <li class="page-item"><a class="page-link" data-target="3" >Price 3</a></li>
                              <li class="page-item"><a class="page-link" data-target="4" >Price 4</a></li>
                              <li class="page-item"><a class="page-link" data-target="5" >All at Once</a></li>
                            </ul>
                          </nav>
                        </div>

                        <div id="columnchart_material" style="width: 1350px; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
@section('css')
@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
    <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@endif
@stop

@section('javascript')

    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif

    <!-- listing all the items related to keyword -->
    <script type="text/javascript">

      const search = document.getElementById('search');
      const matchList = document.getElementById('match-list');

      // searching the keyword in the array list
      const searchItems = async searchText => {
        const items = <?php echo json_encode($itemList); ?>;
        //console.log(items);

        let matches = items.filter(item => {
          const regex = new RegExp(`^${searchText}`, 'gi');
          return item[0].match(regex);
      });

        // Incase the search box is empty
        if(searchText.length === 0) {
          matches = [];
        }

        //console.log(matches);

        outputHtml(matches);
    };

    // Showing the results in the page
    const outputHtml = matches => {
      if(matches.length > 0) {
        const html = matches.map(match => `
          <div class="card card-body">
            <li class="list-group-item">
              <a class="page-link" data-target="4" value="${match[0]}" onclick="selectItem()">
                <span id="name">${match[0]}</span><span class="text-primary">(${match[1]})</span>
              </a>
            </li>
          </div>
          `
        )
        .join('');

        matchList.innerHTML = html;
        //console.log(html);
      }
    }

      search.addEventListener('input',  () => searchItems(search.value));

    </script>

    <script>
      function selectItem() {
        console.log(document.getElementById("name").innerHTML);
        document.getElementById("search").value = document.getElementById("name").innerHTML;
      }
    </script>


    <!-- Price and Chart -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      <!-- Button Action -->

      $(document).ready(function(){

          var trigger = $('#price-nav ul li a'),
              container = $('#columnchart_material');

          trigger.on('click', function(){
            var $this = $(this),
            target = $this.data('target');
            start = target + 1;

            if( target < 5) {
              <!-- Price dataTables -->
              google.charts.load('current', {'packages':['bar']});
              google.charts.setOnLoadCallback(drawChart);

              var array = [];
              function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Vendor Id');
                  data.addColumn('number', 'Price'+target);


                  var arr = <?php echo json_encode($dataset); ?>;
                  //console.log(arr);

                  arr.forEach(function (item, index) {
                    //console.log(item);

                    // for all 4 prices
                    //array.push([ String(item[0]), parseInt(item[2]), parseInt(item[3]), parseInt(item[4]), parseInt(item[5]) ]);

                    // for 1 price at a time
                    array.push([ String(item[0]), parseInt(item[start]) ]);

                  });

                  data.addRows(array);

                    var options = {
                      chart: {
                        title: 'Vendor Price',
                        subtitle: 'Date ',
                      }
                    };

                  var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

                  chart.draw(data, google.charts.Bar.convertOptions(options));
              }

            } else {
              <!-- Price dataTables -->
            google.charts.load('current', {'packages':['bar']});
            google.charts.setOnLoadCallback(drawChart);

              var array = [];
              function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Vendor Id');
                  data.addColumn('number', 'Price1');
                  data.addColumn('number', 'Price2');
                  data.addColumn('number', 'Price3');
                  data.addColumn('number', 'Price4');


                  var arr = <?php echo json_encode($dataset); ?>;
                  //console.log(arr);

                  arr.forEach(function (item, index) {
                    //console.log(item);

                    // for all 4 prices
                    array.push([ String(item[0]), parseInt(item[2]), parseInt(item[3]), parseInt(item[4]), parseInt(item[5]) ]);


                  });

                  data.addRows(array);

                    var options = {
                      chart: {
                        title: 'Vendor Price',
                        subtitle: 'Date ',
                      }
                    };

                  var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

                  chart.draw(data, google.charts.Bar.convertOptions(options));
              }

            }



          });
      });

    </script>

@stop
