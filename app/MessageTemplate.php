<?php

namespace App;

class MessageTemplate extends BaseModel
{
   protected $fillable = [
       'channel',
       'title',
       'message',
   ];

   public static function createTemplate($key, array $data)
   {
      $tp = MessageTemplate::where('message_key', $key)->first();
      if(!$tp)
      {
         $tp = new MessageTemplate;
         $tp->message_key = $key;
      }

      $tp->message = json_encode($data);
      $tp->save();
      return $tp;
   }

   public static function fetch($key)
   {
      return static::where('message_key', $key)->first();
   }

   public function getCleanKey()
   {
      return ucfirst(str_replace('-',' ', $this->message_key));
   }
}
