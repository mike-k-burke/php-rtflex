<?php

namespace RTFLex\tokenizer;

interface ITokenGenerator {

   /**
    * @return mixed
    */
   public function readToken();
}
