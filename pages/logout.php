<?php
/**
 * Logout
 * SUNDARI TOP STAR S.R.L.
 */
require_once '../config/config.php';

logout();
setFlash('success', 'Te-ai deconectat cu succes.');
redirect('/index.php');
