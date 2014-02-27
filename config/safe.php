<?php 
return array (
  'TOKEN_ON' => 'false',
  'TOKEN_NAME' => '__hash__',
  'TOKEN_TYPE' => 'md5',
  'TOKEN_RESET' => 'true',
  'DB_FIELDTYPE_CHECK' => 'true',
  'VAR_FILTERS' => 'htmlspecialchars_deep',//这里由htmlspecialchars改为自定义的函数htmlspecialchars_deep，否则多维表单出错
  
);