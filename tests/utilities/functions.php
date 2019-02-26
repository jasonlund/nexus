<?php

function create($class, $attributes = [], $count = null)
{
    return factoryAction('create', $class, $attributes, $count);
}

function make($class, $attributes = [], $count = null)
{
    return factoryAction('make', $class, $attributes, $count);
}

function raw($class, $attributes = [], $count = null)
{
    return factoryAction('raw', $class, $attributes, $count);
}

function factoryAction($action, $class, $attributes, $count)
{
    return factory("App\\Models\\" . $class, $count)->{$action}($attributes);
}

