<?php


enum eState : int {
    case Unknown = -1;
    case Error = 0;
    case Success = 1;
    case Warning = 2;
}


enum eStatusCode : string {
    case Unknown = "";
    case Ok = "HTTP/1.1 200 OK";
    case NotImplemented = "HTTP/1.1 501 Not Implemented";
    case Error = "HTTP/1.1 500 Internal Server Error";
    case Unauthorized = "HTTP/1.1 401 Unauthorized";
    case UnsupportedMediaType = "HTTP/1.1 415 Unsupported Media Type";
    case Conflict = "HTTP/1.1 409 Conflict";
    case MethodNotAllowed = "HTTP/1.1 Method Not Allowed";
}