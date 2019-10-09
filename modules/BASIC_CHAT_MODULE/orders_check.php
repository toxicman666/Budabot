<?php

if (!isset($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = Setting::get("orders");
if (!empty($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
	$chatBot->send("<red>[ORDERS]<end> <yellow>{$chatBot->data["BASIC_CHAT_MODULE"]["orders"]}<end>",'priv');
?>