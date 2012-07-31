<?php


$columns = array("Leerroute", "Wiki's", "Films", "Toetsen");

$classes = array(
        "Leerroute" => "topics",
        "Wiki's" => "wiki",
        "Films" => "films",
        "Toetsen" => "quizzes");

$sub_columns = array("0009", "0035", "0086", "0004", "0005");

$sub_column_titles = array(
        "0009" => "9 Toets Rekenen met schaal",
        "0035" => "35 Toets Gewervelde dieren",
        "0086" => "86 Toets Tafels van 11, 12 en 25",
        "0004" => "Title 4",
        "0005" => "Title 5"
);

$shown_subs = array("Toetsen");

$show_hide = array("Toetsen");

$users = array(

        //////////////////////////////////////////////////////////////////
    'Yukihiro Matsumoto' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                true, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        
        //////////////////////////////////////////////////////////////////
        'John McCarthy' => array(
                "Leerroute" => array(
                        true, // 0001
                        true, // 0002
                        true, // 0003
                        true, // 004
                        true  // 005
                ),
                "Wiki's" => array(
                        true, // 0001
                        true, // 0002
                        true, // 0003
                        false, // 004
                        false  // 005
                ),
                "Films" => array(
                        true, // 0001
                        true, // 0002
                        true, // 0003
                        true, // 004
                        true  // 005
                ),
                "Toetsen" => array(
                        false, // 0001
                        false, // 0002
                        false, // 0003
                        true, // 004
                        false  // 005
                )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'Edsger Dijkstra' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'Guido van Rossum' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'Ada Lovelace' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'Charles Babbage' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'Alan Turing' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'John von Neumann' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'First name Last Name2' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
    'First name Last Name1' => array(
            "Leerroute" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Wiki's" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                false, // 004
                false  // 005
            ),
            "Films" => array(
                true, // 0001
                true, // 0002
                true, // 0003
                true, // 004
                true  // 005
            ),
            "Toetsen" => array(
                false, // 0001
                false, // 0002
                false, // 0003
                false, // 004
                false  // 005
            )
        ),
        //////////////////////////////////////////////////////////////////
);

/***
 * Function get_status_column
 * Checks column  values and returns it's stauts
 * 
 * @param $column, array of true/false values
 */
function get_status_column($column) {
    $status = 'ok icon-green';

    // Check if there is at least one 'false' in $column
    if (in_array(false, $column)) {
        $status = 'adjust icon-yellow';

        // Check if there is at least one 'true' in $column
        if (!in_array(true, $column)) {
            $status = 'remove icon-red';
        }
    }

    return $status;
}

/**
 * Function get_status
 * Returns status string according to selection
 * 
 * @param $column, array of true/false values
 * @param $sub_column, name of sub column
 */
function get_status($column, $sub_column, $sub_columns) {
    $index = array_search($sub_column, $sub_columns);

    return ($column[$index]) ? 'ok icon-green' : 'remove icon-red';
}
?>
<!doctype html>
<html lang="en">
  <head>
      <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
      <script type="text/javascript" src="js/bootstrap.min.js"></script>
      <script type="text/javascript" src="js/jquery.stickytableheaders.js"></script>
      <script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-tooltip.js"></script>
      <script type="text/javascript" src="js/main.js"></script>
      <link type="text/css" rel="stylesheet" href="css/bootstrap.css" />
      <link type="text/css" rel="stylesheet" href="css/main.css" />
  </head>
  <body>
      <table>
          <thead>
              <tr>
                  <th width="200px" style="padding-left: 10px">
                    <div style="height:55px;"></div>
                    Leerlingen
                  </th>
                  <th width="50px;">&nbsp;</th>
                  <?php
                  foreach ($columns as $column) {
                      $show = in_array($column, $shown_subs);
                      $class = $classes[$column];
                      ?>
                      <th width="80px">
                        <div>
                            <div style="height:40px;overflow:hidden;"><?php echo $column; ?></div>
                            <div style="height:20px;">
                          <?php if (in_array($column, $show_hide)) {?>
                              <?php if (!$show) {?>
                              <a href="#" id="<?php echo $class; ?>" class="show-all" style="color: white">
                              Toon
                                  <i class="icon-arrow-right icon-white"></i>
                                  <?php } else {?>
                                  <a href="#" style="color: white" id="<?php echo $class; ?>" class="show-all open">
                                      <i class="icon-arrow-left icon-white"></i>
                                      Verberg
                                  <?php } ?>
                              <?php }?>
                              </a>
                            </div>
                      </div>
                      <button class="btn btn-warning">
                          <i class="icon icon-ok icon-white"></i>
                      </button>
                      </th>
                      <?php

                      foreach ($sub_columns as $sub_column) {
                          ?>
                  <th class="sub <?php echo $class;?>" <?php if (!$show) {?>style="display: none"<?php }?>>
                      <div style="height:40px;"></div>
                      <div style="height:20px;" class="tip" data-original-title="<?php echo $sub_column_titles[$sub_column]; ?>">
                          <?php echo $sub_column; ?>
                      </div>
                    
                      <button class="btn btn-warning tip" data-original-title="<?php echo $sub_column_titles[$sub_column]; ?>">
                          <i class="icon icon-ok icon-white"></i>
                      </button>
                  </th>
                          <?php 
                      }
                  }
                  ?>
                  
              </tr>
          </thead>
          <tbody>
              <?php foreach ($users as $name=>$data) {?>
              <!---------- row ----------------------------------------------->
              <tr>
                  <td><?php echo $name; ?></td>
                  <td>
                      <button class="btn btn-warning">
                          <i class="icon icon-ok icon-white"></i>
                      </button>
                      
                  </td>
              <?php
              foreach ($columns as $column) {
                  $show = in_array($column, $shown_subs);
                  $class = $classes[$column];
              ?>
                  <td>
                      <a href="#" class="toggler" data-original-title="<?php echo $column; ?>" style="margin-left: 5px;"><i class="icon icon-<?php echo get_status_column($data[$column]); ?>"></i></a>
                  </td>

                  <?php foreach ($sub_columns as $sub_column) {?>
                  <!--  subcol -->
                  <td class="sub <?php echo $class;?>" <?php if (!$show) {?>style="display: none" <?php } ?>>
                      <a href="#" class="toggler" style="margin-left: 5px;">
                      <i class="icon icon-<?php  echo get_status($data[$column], $sub_column, $sub_columns);?>"></i></a>
                  </td>
                  <!-- subcol eof -->
                  <?php } ?>
              <?php } ?>
              </tr>
              <!-- row eof --------------------------------------------------->
              <?php } ?>
              </tbody>
      </table>
  </body>
</html>
