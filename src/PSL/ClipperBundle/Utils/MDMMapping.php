<?php

namespace PSL\ClipperBundle\Utils;

class MDMMapping
{

  public static $mappings = array(
    'countries' => array(
      "Afghanistan" => 1,
      "Aland Islands" => 2,
      "Albania" => 3,
      "Algeria" => 4,
      "American Samoa" => 5,
      "Andorra" => 6,
      "Angola" => 7,
      "Anguilla" => 8,
      "Antarctica" => 9,
      "Antigua and Barbuda" => 10,
      "Argentina" => 11,
      "Armenia" => 12,
      "Aruba" => 13,
      "Australia" => 14,
      "Austria" => 15,
      "Azerbaijan" => 16,
      "Bahamas" => 17,
      "Bahrain" => 18,
      "Bangladesh" => 19,
      "Barbados" => 20,
      "Belarus" => 21,
      "Belgium" => 22,
      "Belize" => 23,
      "Benin" => 24,
      "Bermuda" => 25,
      "Bhutan" => 26,
      "Bolivia" => 27,
      "Bonaire" => 28,
      "Bosnia and Herzegovina" => 29,
      "Botswana" => 30,
      "Bouvet Island" => 31,
      "Brazil" => 32,
      "British Indian Ocean Territory" => 33,
      "British Virgin Islands" => 34,
      "Brunei Darussalam" => 35,
      "Bulgaria" => 36,
      "Burkina Faso" => 37,
      "Burundi" => 38,
      "Cambodia" => 39,
      "Cameroon" => 40,
      "Canada" => 41,
      "Cape Verde" => 42,
      "Cayman Islands" => 43,
      "Central African Republic" => 44,
      "Chad" => 45,
      "Chile" => 46,
      "China" => 47,
      "Christmas Island" => 48,
      "Cocos (Keeling) Islands" => 49,
      "Colombia" => 50,
      "Comoros" => 51,
      "Congo-Brazzaville" => 52,
      "Congo-Kinshasa" => 53,
      "Cook Islands" => 54,
      "Costa Rica" => 55,
      "Cote d'Ivoire" => 56,
      "Croatia" => 57,
      "Cuba" => 58,
      "Curacao" => 59,
      "Cyprus" => 60,
      "Czech" => 61,
      "Denmark" => 62,
      "Djibouti" => 63,
      "Dominica" => 64,
      "Dominican Republic" => 65,
      "Ecuador" => 66,
      "Egypt" => 67,
      "El Salvador" => 68,
      "Equatorial Guinea" => 69,
      "Eritrea" => 70,
      "Estonia" => 71,
      "Ethiopia" => 72,
      "Falkland Islands" => 73,
      "Faroe Islands" => 74,
      "Fiji" => 75,
      "Finland" => 76,
      "France" => 77,
      "French Guiana" => 78,
      "French Polynesia" => 79,
      "French Southern Territories" => 80,
      "Gabon" => 81,
      "Gambia" => 82,
      "Georgia" => 83,
      "Germany" => 84,
      "Ghana" => 85,
      "Gibraltar" => 86,
      "Greece" => 87,
      "Greenland" => 88,
      "Grenada" => 89,
      "Guadeloupe" => 90,
      "Guam" => 91,
      "Guatemala" => 92,
      "Guernsey" => 93,
      "Guinea" => 94,
      "Guinea-Bissau" => 95,
      "Guyana" => 96,
      "Haiti" => 97,
      "Heard and McDonald Islands" => 98,
      "Honduras" => 99,
      "Hong Kong" => 100,
      "Hungary" => 101,
      "Iceland" => 102,
      "India" => 103,
      "Indonesia" => 104,
      "Iran" => 105,
      "Iraq" => 106,
      "Ireland" => 107,
      "Isle of Man" => 108,
      "Israel" => 109,
      "Italy" => 110,
      "Jamaica" => 111,
      "Japan" => 112,
      "Jersey" => 113,
      "Jordan" => 114,
      "Kazakhstan" => 115,
      "Kenya" => 116,
      "Kiribati" => 117,
      "Korea" => 118,
      "Kuwait" => 119,
      "Kyrgyzstan" => 120,
      "Laos" => 121,
      "Latvia" => 122,
      "Lebanon" => 123,
      "Lesotho" => 124,
      "Liberia" => 125,
      "Libya" => 126,
      "Liechtenstein" => 127,
      "Lithuania" => 128,
      "Luxembourg" => 129,
      "Macau" => 130,
      "Macedonia" => 131,
      "Madagascar" => 132,
      "Malawi" => 133,
      "Malaysia" => 134,
      "Maldives" => 135,
      "Mali" => 136,
      "Malta" => 137,
      "Marshall Islands" => 138,
      "Martinique" => 139,
      "Mauritania" => 140,
      "Mauritius" => 141,
      "Mayotte" => 142,
      "Mexico" => 143,
      "Micronesia" => 144,
      "Moldova" => 145,
      "Monaco" => 146,
      "Mongolia" => 147,
      "Montenegro" => 148,
      "Montserrat" => 149,
      "Morocco" => 150,
      "Mozambique" => 151,
      "Myanmar" => 152,
      "Namibia" => 153,
      "Nauru" => 154,
      "Nepal" => 155,
      "Netherlands" => 156,
      "New Caledonia" => 157,
      "New Zealand" => 158,
      "Nicaragua" => 159,
      "Niger" => 160,
      "Nigeria" => 161,
      "Niue" => 162,
      "Norfolk Island" => 163,
      "North Korea" => 164,
      "Northern Mariana Islands" => 165,
      "Norway" => 166,
      "Oman" => 167,
      "Other" => 168,
      "Pakistan" => 169,
      "Palau" => 170,
      "Palestine" => 171,
      "Panama" => 172,
      "Papua New Guinea" => 173,
      "Paraguay" => 174,
      "Peru" => 175,
      "Philippines" => 176,
      "Pitcairn" => 177,
      "Poland" => 178,
      "Portugal" => 179,
      "Puerto Rico" => 180,
      "Qatar" => 181,
      "Reunion" => 182,
      "Romania" => 183,
      "Russia" => 184,
      "Rwanda" => 185,
      "Saint Barthelemy" => 186,
      "Saint Helena" => 187,
      "Saint Kitts and Nevis" => 188,
      "Saint Lucia" => 189,
      "Saint Martin" => 190,
      "Saint Pierre and Miquelon" => 191,
      "Saint Vincent and The Grenadines" => 192,
      "Samoa" => 193,
      "San Marino" => 194,
      "Sao Tome and Principe" => 195,
      "Saudi Arabia" => 196,
      "Senegal" => 197,
      "Serbia" => 198,
      "Seychelles" => 199,
      "Sierra Leone" => 200,
      "Singapore" => 201,
      "Sint Maarten" => 202,
      "Slovakia" => 203,
      "Slovenia" => 204,
      "Solomon Islands" => 205,
      "Somalia" => 206,
      "South Africa" => 207,
      "South Georgia and the South Sandwich Islands" => 208,
      "South Sudan" => 209,
      "Spain" => 210,
      "Sri Lanka" => 211,
      "Sudan" => 212,
      "Suriname" => 213,
      "Svalbard and Jan Mayen" => 214,
      "Swaziland" => 215,
      "Sweden" => 216,
      "Switzerland" => 217,
      "Syria" => 218,
      "Taiwan" => 219,
      "Tajikistan" => 220,
      "Tanzania" => 221,
      "Thailand" => 222,
      "Timor-Leste" => 223,
      "Togo" => 224,
      "Tokelau" => 225,
      "Tonga" => 226,
      "Trinidad and Tobago" => 227,
      "Tunisia" => 228,
      "Turkey" => 229,
      "Turkmenistan" => 230,
      "Turks and Caicos Islands" => 231,
      "Tuvalu" => 232,
      "UAE" => 233,
      "Uganda" => 234,
      "UK" => 235,
      "Ukraine" => 236,
      "United States Minor Outlying Islands" => 237,
      "United States Virgin Islands" => 238,
      "Uruguay " => 239,
      "USA" => 240,
      "Uzbekistan" => 241,
      "Vanuatu" => 242,
      "Vatican" => 243,
      "Venezuela" => 244,
      "Viet Nam" => 245,
      "Wallis and Futuna" => 246,
      "Western Sahara" => 247,
      "Yemen" => 248,
      "Zambia" => 249,
      "Zimbabwe" => 250,
      ),
    'specialties' => array(
      'Abdominal Radiology' => 1,
      'Abdominal Surgery' => 2,
      'Addiction Medicine' => 3,
      'Addiction Psychiatry' => 4,
      'Adolescent Medicine' => 5,
      'Adult Reconstructive Orthopaedics' => 6,
      'Aerospace Medicine' => 7,
      'Allergy' => 8,
      'Allergy/Immunology' => 9,
      'Anatomic & Clinical Pathology' => 10,
      'Anatomic Pathology' => 11,
      'Anaesthesiology' => 12,
      'Bariatric Surgery' => 13,
      'Blood Banking/Transfusion Medicine' => 14,
      'Bone Marrow Transplantation' => 15,
      'Cardiac Electrophysiology' => 16,
      'Cardiac Surgery' => 17,
      'Cardiology' => 18,
      'Cardio/Thoracic Surgery' => 19,
      'Chemical Pathology' => 20,
      'Clinical & Lab Immunology' => 21,
      'Clinical Biochemical Genetics' => 22,
      'Clinical Cytogenetics' => 23,
      'Clinical Genetics' => 24,
      'Clinical Molecular Genetics' => 25,
      'Clinical Neurophysiology' => 26,
      'Clinical Pathology' => 27,
      'Clinical Pharmacology' => 28,
      'Colorectal Surgery' => 29,
      'Community Medicine' => 30,
      'Cosmetic Dermatology' => 31,
      'Craniofacial Surgery' => 32,
      'Critical Care Medicine' => 33,
      'Critical Care Surgery' => 34,
      'Cytopathology' => 35,
      'Dermatologic Surgery' => 36,
      'Dermatology' => 37,
      'Dermatopathology' => 38,
      'Developmental-Behavioral Paediatrics' => 39,
      'Diabetes' => 40,
      'Diagnostic Radiology' => 41,
      'Electroencephalography' => 42,
      'Electrophysiology' => 43,
      'Emergency Medicine' => 44,
      'Endocrinology' => 45,
      'Epidemiology' => 46,
      'Experimental Pathology' => 47,
      'Family Practice' => 48,
      'Foot & Ankle Orthopaedics' => 49,
      'Forensic Medicine' => 50,
      'Forensic Pathology' => 51,
      'Forensic Psychiatry' => 52,
      'Gastroenterology' => 53,
      'General Pathology' => 54,
      'GPs/PCPs' => 55,
      'General Surgery' => 56,
      'Genetics' => 57,
      'Genitourinary Disorders' => 58,
      'Geriatric Medicine' => 59,
      'Geriatric Psychiatry' => 60,
      'Geriatrics' => 61,
      'Glaucoma Ophthalmology' => 62,
      'Gynaecological Oncology' => 63,
      'Gynaecological Surgery' => 64,
      'Gynaecological Urology' => 65,
      'Gynaecology' => 66,
      'Hand Surgery' => 67,
      'Head & Neck Surgery' => 68,
      'Haematology' => 69,
      'Haematology-Oncology' => 70,
      'Hepatology' => 71,
      'Infectious Disease, incl HIV/Aids' => 76,
      'Hospitalist' => 73,
      'Immunology' => 74,
      'Industrial Medicine' => 75,
      'Intensive Care' => 77,
      'Internal Medicine' => 78,
      'Cardiology - Interventional' => 79,
      'Laser Surgery' => 80,
      'Legal Medicine' => 81,
      'Maternal & Fetal Medicine' => 82,
      'Medical Genetics' => 83,
      'Medical Management' => 84,
      'Medical Microbiology' => 85,
      'Medical Physiology' => 86,
      'Medical Toxicology' => 87,
      'Metabolism' => 88,
      'Military Medicine' => 89,
      'Molecular Genetic Pathology' => 90,
      'Neonatal-Perinatal Medicine' => 91,
      'Nephrology' => 92,
      'Neurodevelopmental Disabilities' => 93,
      'Neurology' => 94,
      'Neurology & Psychiatry' => 95,
      'Neurology/Diagnostic Radiology/Neuroradiology' => 96,
      'Neurology/Rehabilitation' => 97,
      'Neuropathology' => 98,
      'Neuropsychology' => 99,
      'Neuroradiology' => 100,
      'Neurosurgery' => 101,
      'Nuclear Cardiology' => 102,
      'Nuclear Medicine' => 103,
      'Nuclear Radiology' => 104,
      'Obstetrics' => 105,
      'Obstetrics/Gynaecology' => 106,
      'Occupational Medicine' => 107,
      'Oncology' => 108,
      'Ophthalmology' => 109,
      'Oral & Maxillofacial Surgery' => 110,
      'Oral Pathology' => 111,
      'Orthopaedic Surgery' => 112,
      'Orthopaedic Surgery of the Spine' => 113,
      'Orthopaedic Trauma' => 114,
      'Orthopaedics' => 115,
      'Osteopathic Manipulative Medicine' => 116,
      'Otolaryngology' => 117,
      'Otology' => 118,
      'Pain Medicine' => 119,
      'Palliative Medicine' => 120,
      'Parasitology' => 121,
      'Parkinson\'s Disease' => 122,
      'Pathology' => 123,
      'Paediatric & Adolescent Psychiatry' => 124,
      'Paediatric Allergy' => 125,
      'Paediatric Anaesthesiology' => 126,
      'Paediatric Cardiology' => 127,
      'Paediatric Cardiothoracic Surgery' => 128,
      'Paediatric Critical Care Medicine' => 129,
      'Paediatric Dermatology' => 130,
      'Paediatric Emergency Medicine' => 131,
      'Paediatric Endocrinology' => 132,
      'Paediatric Gastroenterology' => 133,
      'Paediatric General Surgery' => 134,
      'Paediatric Haematology' => 135,
      'Paediatric Haematology-Oncology' => 136,
      'Paediatric Infectious Diseases' => 137,
      'Paediatric Nephrology' => 138,
      'Paediatric Neurology' => 139,
      'Paediatric Oncology' => 140,
      'Paediatric Ophthalmology' => 141,
      'Paediatric Orthopaedics' => 142,
      'Paediatric Otolaryngology' => 143,
      'Paediatric Pathology' => 144,
      'Paediatric Psychiatry' => 145,
      'Paediatric Pulmonology' => 146,
      'Paediatric Radiology' => 147,
      'Paediatric Rehabilitation Medicine' => 148,
      'Paediatric Rheumatology' => 149,
      'Paediatric Surgery' => 150,
      'Paediatric Urology' => 151,
      'Paediatrics' => 152,
      'Perinatology' => 153,
      'Pharmaceutical Medicine' => 154,
      'Physical Medicine & Rehabilitation' => 155,
      'Plastic Surgery' => 156,
      'Preventive Medicine' => 157,
      'Proctology' => 158,
      'Psychiatry' => 159,
      'Psychosomatic Medicine' => 160,
      'Public Health Medicine' => 161,
      'Radiation Oncology' => 162,
      'Radiological Physics' => 163,
      'Radiology' => 164,
      'Reproductive Endocrinology' => 165,
      'Research' => 166,
      'Respirology/Pulmonology' => 167,
      'Rheumatology' => 168,
      'Selective Pathology' => 169,
      'Sleep Medicine' => 170,
      'Spinal Cord Injury' => 171,
      'Sports Medicine' => 172,
      'Surgical Oncology' => 173,
      'Thoracic Surgery' => 174,
      'Transplantation Surgery' => 175,
      'Trauma Surgery' => 176,
      'Tropical Diseases' => 177,
      'Tuberculosis' => 178,
      'Undersea Medicine & Hyperbaric Medicine' => 179,
      'Unknown' => 180,
      'Urology' => 181,
      'Vascular & Interventional Radiology' => 182,
      'Vascular Medicine' => 183,
      'Vascular Neurology' => 184,
      'Vascular Surgery' => 185,
      'Virology' => 186,
      'Vitreoretinal Medicine' => 187,
      'Academic Pharmacist' => 188,
      'Ambulatory Care Pharmacist' => 189,
      'Biochemistry' => 190,
      'Biology' => 191,
      'Biomedicine' => 192,
      'Certified Diabetes Educator' => 193,
      'Certified Nurse Midwife' => 194,
      'Certified Nursing Assistant' => 195,
      'Certified Registered Nurse Anaesthetist' => 196,
      'Chiropodiatry' => 197,
      'Chiropracter' => 198,
      'Clinical & Lab Dermatological Immunology' => 199,
      'Clinical Chemistry' => 200,
      'Clinical Nurse Specialist' => 201,
      'Coroner' => 202,
      'Endodontics' => 203,
      'General Dentistry' => 204,
      'Physician Assistant' => 205,
      'Government Pharmacist' => 206,
      'Haematologic Malignancies' => 207,
      'Home Care Pharmacist' => 208,
      'Homeopathy' => 209,
      'Hospice Pharmacist' => 210,
      'Hospital Pharmacist' => 211,
      'Independent Community Pharmacist' => 212,
      'Industrial Pharmacist' => 213,
      'Licensed Practical Nurse' => 214,
      'Licensed Vocational Nurse' => 215,
      'Long Term Care Pharmacist' => 216,
      'Managed Care Pharmacist' => 217,
      'Medical Assistant' => 218,
      'Medical Management / Administration' => 219,
      'Medical Research Science' => 220,
      'Medical Student' => 221,
      'Medical Technician' => 222,
      'Military Pharmacist' => 223,
      'Nurse Practitioners' => 224,
      'Nutritionist / Dietician' => 225,
      'Oncology Nursing' => 226,
      'Oncology Pharmacist' => 227,
      'Optometry' => 228,
      'Orthodontics' => 229,
      'Paediatric Dentistry' => 230,
      'Periodontics' => 231,
      'Pharmacist' => 232,
      'Podiatry' => 233,
      'Prosthodontics' => 234,
      'Psychoanalysis' => 235,
      'Psychology' => 236,
      'Psychotherapy' => 237,
      'Registered Nurse' => 238,
      'Retail Community Pharmacist' => 239,
      'Veterinarian' => 240,
      'Veterinary Pharmacist' => 241,
      "None" => "",
    )
  );

  /**
   * @param $type string "countries" or "specialties"
   * @param $search string name
   * @return false or code
   */
  public static function map($type, $search = '') {
    return isset(self::$mappings[$type][$search]) ? self::$mappings[$type][$search] : FALSE;
  }

  /**
   * @param $type string "countries" or "specialties"
   * @param $searches array of string names
   * @return false or code
   */
  public static function map_multiple($type, $searches = array()) {
    $map = array();
    foreach ($searches as $search) {
      $map[$search] = isset(self::$mappings[$type][$search]) ? self::$mappings[$type][$search] : FALSE;
    }
    return $map;
  }
}
