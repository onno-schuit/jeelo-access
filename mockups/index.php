<?php


$columns = array("Leerroute met eenheden", "Wiki's", "Films", "Toetsen");

$classes = array(
        "Leerroute met eenheden" => "topics",
        "Wiki's" => "wiki",
        "Films" => "films",
        "Toetsen" => "quizzes");

$sub_columns = array("0001", "0002", "0003", "0004", "0005");

$shown_subs = array("Toetsen");

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
                  <th width="200px">Person</th>
                  <?php
                  foreach ($columns as $column) {
                      $show = in_array($column, $shown_subs);
                      $class = $classes[$column];
                      ?>
                      <th><?php echo $column; ?><br />
                      <?php if (!$show) {?>
                      <a href="#" id="<?php echo $class; ?>" class="show-all" style="color: white">
                      Toon
                      <i class="icon-arrow-right icon-white"></i>
                      <?php } else {?>
                      <a href="#" style="color: white" id="<?php echo $class; ?>" class="show-all open">
                          <i class="icon-arrow-left icon-white"></i>
                          Verberg
                      <?php } ?>
                      </a><br />
                      <a href="#" class="global-toggler"><i class="icon icon-ok icon-white"></i></a>
                      </th>
                      <?php

                      foreach ($sub_columns as $sub_column) {
                          ?>
                  <th class="sub <?php echo $class;?>" <?php if (!$show) {?>style="display: none"<?php }?>>&nbsp;<br />
                      <?php echo $sub_column; ?><br />
                       <a href="#" class="global-toggler"><i class="icon icon-ok icon-white"></i></a>
                  </th>
                          <?php 
                      }
                  }
                  ?>
                  
              </tr>
          </thead>
          <tbody>
              <?php for ($i=0; $i<50; $i++) {?>
              <!---------- row ----------------------------------------------->
              <tr>
                  <td>Last Name First Name
                      <a href="#" class="toggler" style="margin-left:20px;"><i class="icon icon-ok icon-green"></i></a>
                  </td>
              <?php
              foreach ($columns as $column) {
                  $show = in_array($column, $shown_subs);
                  $class = $classes[$column];
              ?>
                  <td>
                      <a href="#" class="toggler" data-original-title="<?php echo $column; ?>"><i class="icon icon-ok icon-green"></i></a>
                  </td>

                  <?php foreach ($sub_columns as $sub_column) {?>
                  <!--  subcol -->
                  <td class="sub <?php echo $class;?>" <?php if (!$show) {?>style="display: none" <?php } ?>>
                      <a href="#" class="toggler" data-original-title="<?php echo $sub_column; ?>"><i class="icon icon-ok icon-green"></i></a>
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
