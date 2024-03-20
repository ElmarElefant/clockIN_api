<?php


class UsersController extends BaseController {

    function tableName() : string {
        return 'users';
    }

    function publicActions() : array {
        return [];
    }

    function adminActions() : array {
        return ['*'];
    }

}


class NotesController extends BaseController {

    function tableName() : string {
        return 'notes';
    }

    function publicActions() : array {
        return [
            'selectAllAction',
            'selectIdAction'
        ];
    }

    function adminActions() : array {
        return [];
    }

}


class TestController extends BaseController {

    function tableName() : string {
        return 'test';
    }

    function publicActions() : array {
        return [
            'selectAllAction',
            'selectIdAction'
        ];
    }

    function adminActions() : array {
        return [];
    }

}