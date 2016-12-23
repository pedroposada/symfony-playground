<?php

namespace PP\SampleBundle\Utils;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ClipperSerializer
{
  /**
   * @return \Symfony\Component\Serializer\Serializer $serializer
   */
  public function serializer()
  {
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());
    $serializer = new Serializer($normalizers, $encoders);
    return $serializer;
  }
  
  /**
   * @param $raw string
   * @param $format string
   * @return mixed
   */
  public function decode($raw, $format = 'json')
  {
    return $this->serializer()->decode($raw, $format);
  }
  
  /**
   * @param $raw string
   * @param $format string
   * @return string json
   */
  public function encode($raw, $format = 'json')
  {
    return $this->serializer()->encode($raw, $format);
  }
}
