<?php

namespace Steodec\Controllers;

class Constants {
    const METHOD_GET         = "GET";
    const METHOD_POST        = "POST";
    const METHOD_PUT         = "PUT";
    const METHOD_DELETE      = "DELETE";
    const PUBLIC_PATH        = "/public";
    const UPLOAD_PATH        = "public/upload";
    const JS_PATH            = self::PUBLIC_PATH . "/assets/js";
    const CSS_PATH           = self::PUBLIC_PATH . "/assets/css";
    const IMG_PATH           = self::PUBLIC_PATH . "/assets/img";
    const TEMPLATE_PATH      = "./src/views/";
    const BASE_TEMPLATE_PATH = "./src/views/";
}