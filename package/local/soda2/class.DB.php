<?php

function objectToArray($d) {
  if (is_object($d)) {
    // Gets the properties of the given object
    // with get_object_vars function
    $d = get_object_vars($d);
  }

  if (is_array($d)) {
    /*
     * Return array converted to object
     * Using __FUNCTION__ (Magic constant)
     * for recursive call
     */
    return array_map(__FUNCTION__, $d);		
  } else {
    // Return array
    return $d;
  }
}


class Soda2_DB {
  function __construct($DB) {
    $this->db = $DB;
  }

  function sql($sql, $force_one=false) {
    $tmp = $this->db->get_records_sql($sql);

    $result = objectToArray($tmp);

    if ($force_one && is_array($result) && count($result) == 1) {
      $data = null;
      foreach ($result as $key=>$data) {
      }
      return $data;
    }

    if (is_array($result) && !count($result)) {
      return Null;
    }
    return $result;
  }

  function update($table, $array) {
    $data = (object)$array;

    $this->db->update_record($table, $data);
  }

  function insert($table, $array) {
    $data = (object)$array;

    return $this->db->insert_record($table, $array);
  }

  function record($table, $params) {
    return objectToArray($this->db->get_record($table, $params));
  }

}