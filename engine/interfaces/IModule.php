<?php
namespace ksf;

interface IModule {

    function getData($parameters = null); // должно вернуть, в зависимости от параметров, данные для шаблона и задать внутри класса имя шаблона, которое вернет getTmp();
    function getTmp(); // вернет шаблон

}

