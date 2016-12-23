<?php

namespace PP\SampleBundle\Utils;

class Quota 
{
	/**
	 * Lookup combinations in quota.yml
	 * @param $countries string|array of names
	 * @param $specialties string|array of names
	 * @return $quotas
	 */
	public function lookupMultiple($countries, $specialties)
	{
		$quotas = array();
		foreach ((array)$countries as $country) {
			foreach ((array)$specialties as $specialty) {
				$quotas[] = $this->lookupOne($country, $specialty);
			}
		}
    	 
  	return $quotas;
	}

	/**
	 * Lookup combination in quota.yml
	 * @param $country string name
	 * @param $specialty string name
	 * @return $quota
	 */
	public function lookupOne($country, $specialty, $default = 1)
	{
		$country = \PP\SampleBundle\Utils\MDMMapping::map('countries', $country);
		$specialty = \PP\SampleBundle\Utils\MDMMapping::map('specialties', $specialty);
		$yaml = new \Symfony\Component\Yaml\Parser();
  	$lookup = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/config/quota.yml'));
    	 
  	return isset($lookup[$country][$specialty]) ? $lookup[$country][$specialty] : $default;
	}
}