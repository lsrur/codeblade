<?php

function cbParam($param)
{
  $params = \Config::get('cb_params', []);
  return $params[$param] ?? false;
}

function cbRoute($value)
{
  return '<?php echo "pito"; ?>';
}
