@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->getTranslatedAttribute('display_name_plural'))

<?php
  $customerName = "";
  $vendorName = "";
  $customList = [];
  $vendorList = [];
 ?>


@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->getTranslatedAttribute('display_name_plural') }}
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
          <!-- customer info section -->
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 class="text-center">ORDER INFO</h3>
                      <!-- Function for extracting customer info -->
                      <?php
                        function query($dataTypeContent, $dataType, $customerName, $vendorName) {
                          $count = 0;
                          $total = 0;
                          foreach ($dataTypeContent as $data) {
                            if ($data->customer_name == $customerName && $data->vendor_name == $vendorName) {
                              $count++;
                              $total = $total + ($data->order_total);
                              $orderInfo = [];
                              array_push($orderInfo, $data->customer_id, $data->customer_name, $data->vendor_name, $total, $count);
                            }
                          }
                          return $orderInfo;
                        }
                      ?>

                      <!-- Sending Get request to store user response related data on the page for customer  -->
                      <?php

                        if (isset($_GET['customer-name'], $_GET['vendor-name'])) {
                          $customername = $_GET['customer-name'];
                          $vendorname = $_GET['vendor-name'];
                          $orderInfo = query($dataTypeContent, $dataType, $customername, $vendorname);
                          //print_r($orderInfo);
                        } else {
                          $orderInfo = ['ID', TRUE, TRUE, 00, 00 ];
                        }

                      ?>

                      <!-- search from field -->
                      <form class="form-inline text-center" action="" method="GET">
                        <datalist id="match-list">
                          <!-- insert suggestion -->
                        </datalist>
                        <datalist id="vendor-list">
                          <!-- insert suggestion -->
                        </datalist>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="search" name="customer-name" placeholder="Customer name" list="match-list" autocomplete="off">
                        <input type="text" class="form-control mb-2 mr-sm-2" id="search" name="vendor-name" placeholder="Vendor name" list="vendor-list" autocomplete="off">
                        <button type="submit" class="btn btn-primary mb-2" value="Submit">Submit</button>
                      </form>

                    <table class="table table-borderless text-center">
                      <tbody>
                        <tr>
                          <td><h4>CUSTOMER : <?php echo $orderInfo[1] ?> (<?php echo $orderInfo[0] ?>)</h4></td>
                          <td><h4>VENDOR: <?php echo $orderInfo[2] ?></h4></td>
                        </tr>
                        <tr>
                          <td><h4>TOTAL ORDER: <?php echo $orderInfo[4] ?></h4></td>
                          <td><h4>TOTAL AMOUNT: ₹<?php echo $orderInfo[3] ?></h4></td>
                        </tr>
                        <tr>
                          <td colspan="2">Last Order:</td>
                        </tr>
                      </tbody>
                    </table>
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        @foreach($dataType->browseRows as $row)
                                        <th>
                                            @if ($isServerSide)
                                                <a href="{{ $row->sortByUrl($orderBy, $sortOrder) }}">
                                            @endif
                                            {{ $row->getTranslatedAttribute('display_name') }}
                                            @if ($isServerSide)
                                                @if ($row->isCurrentSortField($orderBy))
                                                    @if ($sortOrder == 'asc')
                                                        <i class="voyager-angle-up pull-right"></i>
                                                    @else
                                                        <i class="voyager-angle-down pull-right"></i>
                                                    @endif
                                                @endif
                                                </a>
                                            @endif
                                        </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTypeContent as $data)

                                      <?php
                                        // storing all the unique customers
                                        if (!in_array($data->customer_name, array_column($customList, '1')))
                                        {
                                            $customName = [];
                                            array_push($customName, $data->customer_id, $data->customer_name);
                                            array_push($customList, $customName);
                                        }
                                        //print_r($customList);
                                     ?>

                                     <?php
                                       // storing all the unique vendors
                                       if (!in_array($data->vendor_name, $vendorList))
                                       {

                                           array_push($vendorList, $data->vendor_name);
                                       }
                                       //print_r($vendorList);
                                    ?>

                                    <!-- displaying only the desired records -->
                                    @if ($data->customer_name == $orderInfo[1] && $data->vendor_name == $orderInfo[2])
                                    <tr>
                                        @foreach($dataType->browseRows as $row)
                                            @php
                                            if ($data->{$row->field.'_browse'}) {
                                                $data->{$row->field} = $data->{$row->field.'_browse'};
                                            }
                                            @endphp
                                            <td>
                                                @if (isset($row->details->view))
                                                    @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $data->{$row->field}, 'action' => 'browse'])
                                                @elseif($row->type == 'relationship')
                                                    @include('voyager::formfields.relationship', ['view' => 'browse','options' => $row->details])
                                                @elseif($row->type == 'date' || $row->type == 'timestamp')
                                                    @if ( property_exists($row->details, 'format') && !is_null($data->{$row->field}) )
                                                        {{ \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($row->details->format) }}
                                                    @else
                                                        {{ $data->{$row->field} }}
                                                    @endif
                                                @elseif($row->type == 'text')
                                                <!-- edited browse -->
                                                @if($row->display_name == 'Inventory' || $row->display_name == 'Order Details' || $row->display_name == 'Order Details Sup' || $row->display_name == 'Invoice Data' || $row->display_name == 'Items')
                                                        @if(json_decode($data->{$row->field}) == null)
                                                            <p>NULL</p>
                                                        @else
                                                        <!-- Button trigger modal -->
                                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target=<?php echo '.'.str_replace(' ', '', $row->display_name).round(microtime(true) * 1000); ?> >
                                                            View
                                                        </button>
                                                        <div class="<?php echo str_replace(' ', '', $row->display_name).round(microtime(true) * 1000);  ?> modal modal-info fade in" tabindex="-1" role="dialog">
                                                        <div class="modal-dialog" style="width: 80%;">
                                                        <div class="modal-content">
                                                            <?php

                                                            $jsonData = $data->{$row->field};
                                                            $array = new RecursiveIteratorIterator(
                                                            new RecursiveArrayIterator(json_decode($jsonData, TRUE)),
                                                            RecursiveIteratorIterator::SELF_FIRST);

                                                            ?>
                                                         <div class="modal-header">
                                                            <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                                                               <span aria-hidden="true">×</span>
                                                            </button>
                                                            <h4 class="modal-title">
                                                                <?php echo $row->display_name; ?>
                                                            </h4>
                                                         </div>
                                                         <div class="modal-body" style="overflow: scroll;">

                                                            <table class="table table-striped">
                                                               <thead>
                                                                    <tr>
                                                                    <?php
                                                                    foreach ($array as $key => $val) {
                                                                    if($key == "1"){break;}
                                                                        if(is_array($val)) {
                                                                        } else {
                                                                            echo "<th>$key</th>";
                                                                        }
                                                                      }
                                                                    ?>
                                                                    </tr>
                                                               </thead>
                                                               <tbody>
                                                                <?php

                                                                foreach ($array as $key => $val)    {
                                                                    if(is_array($val)){
                                                                        $result = strcmp(next($val), prev($val));
                                                                        if( $result != '0' ){
                                                                        echo "<tr>";    }
                                                                        else {  break;  }
                                                                    }
                                                                    else    {
                                                                        if(empty($val)) {
                                                                            echo "<td><b>-</b></td>";
                                                                        }
                                                                        else    {
                                                                            echo "<td>$val</td>";
                                                                        }
                                                                        }
                                                                    }

                                                               echo "</tbody>";
                                                            echo"</table>";

                                                                    ?>
                                                        </div>
                                                        <div class="modal-footer"><button type="button" data-dismiss="modal" class="btn btn-outline pull-right">Close</button>
                                                        </div>
                                                        </div>
                                                        </div>
                                                    <!--done-->
                                                    @endif
                                                    @else
                                                      @include('voyager::multilingual.input-hidden-bread-browse')
                                                      <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                                    @endif
                                                    <!--URL field-->
                                                @elseif($row->type == 'url')
                                                        @include('voyager::multilingual.input-hidden-bread-browse')
                                                    @if(!empty($data->{$row->field}))
                                                        <div>
                                                        <button type="button" class="btn btn-warning">
                                                            <a href = "{{ $data->{$row->field} }}" target="_blank" style="color: white;">View</a></button>
                                                        </div>
                                                    @else
                                                         <p>NULL</p>
                                                    @endif

                                                    <!-- edited browse -->

                                                @elseif($row->type == 'text_area')
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                                @else
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <span>{{ $data->{$row->field} }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endif
                                  @endforeach
                                </tbody>
                            </table>
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
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif

    <!-- search for customer -->
    <script type="text/javascript">
      const matchList = document.getElementById('match-list');

      const items = <?php echo json_encode($customList); ?>;
      //console.log(items);

      const html = items.map(item => `
              <option value="${item[1]}">
        `
      )
      .join('');

      matchList.innerHTML = html;
      //console.log(html);

    </script>

    <!-- search for vendor -->
    <script type="text/javascript">
      const vendorList = document.getElementById('vendor-list');

      const vendors = <?php echo json_encode($vendorList); ?>;
      //console.log(items);

      const vendorOption = vendors.map(vendor => `
              <option value="${vendor}">
        `
      )
      .join('');

      vendorList.innerHTML = vendorOption;
      console.log(vendorOption);

    </script>


    <script>
        $(document).ready(function () {
            @if (!$dataType->server_side)
                var table = $('#dataTable').DataTable({!! json_encode(
                    array_merge([
                        "order" => $orderColumn,
                        "language" => __('voyager::datatable'),
                        "columnDefs" => [['targets' => -1, 'searchable' =>  false, 'orderable' => false]],
                    ],
                    config('voyager.dashboard.data_tables', []))
                , true) !!});
            @else
                $('#search-input select').select2({
                    minimumResultsForSearch: Infinity
                });
            @endif

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
                //Reinitialise the multilingual features when they change tab
                $('#dataTable').on('draw.dt', function(){
                    $('.side-body').data('multilingual').init();
                })
            @endif
            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked')).trigger('change');
            });
        });




        @if($usesSoftDeletes)
            @php
                $params = [
                    's' => $search->value,
                    'filter' => $search->filter,
                    'key' => $search->key,
                    'order_by' => $orderBy,
                    'sort_order' => $sortOrder,
                ];
            @endphp
            $(function() {
                $('#show_soft_deletes').change(function() {
                    if ($(this).prop('checked')) {
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 1]), true)) }}"></a>');
                    }else{
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 0]), true)) }}"></a>');
                    }

                    $('#redir')[0].click();
                })
            })
        @endif
        $('input[name="row_id"]').on('change', function () {
            var ids = [];
            $('input[name="row_id"]').each(function() {
                if ($(this).is(':checked')) {
                    ids.push($(this).val());
                }
            });
            $('.selected_ids').val(ids);
        });
    </script>
@stop
