<?php
/**
 * Definition of the form fields for all LP's
 * WARNING: We still have some 'agreed upon' conventions that are not depicted in the field definitions:
 * 1) When country != US, we do not require a state value. This is true for both shipping and billing fields
 *
 */
class CCDPostParserFormField{
    /*
     * Consts for the possible values of $this->type
     */
    const FIELD_TYPE_BOOLEAN = 1;
    const FIELD_TYPE_INTEGER = 2;
    const FIELD_TYPE_FLOAT = 3;
    const FIELD_TYPE_STRING = 4;
    const FIELD_TYPE_EMAIL = 5;
    const FIELD_TYPE_SELECT = 6;
    const FIELD_TYPE_STATE = 7;
    const FIELD_TYPE_COUNTRY = 8;
    const FIELD_TYPE_HIDDEN = 9;
    const FIELD_TYPE_CREDITCARD_NUMBER = 10;

    public $name = '';
    public $label = '';
    public $required = false;
    public $type = '';
    public $options = null;
    
    public $oldValue = null;

    public function __construct($name, $label, $type, $required, $options=array()){
        $this->name = $name;
        $this->label = $label;
        $this->required = $required ? true : false;

        switch($type){
            case self::FIELD_TYPE_BOOLEAN:
                $this->type = self::FIELD_TYPE_BOOLEAN;
                break;
            case self::FIELD_TYPE_INTEGER:
                $this->type = self::FIELD_TYPE_INTEGER;
                break;
            case self::FIELD_TYPE_EMAIL:
                $this->type = self::FIELD_TYPE_EMAIL;
                break;
            case self::FIELD_TYPE_SELECT:
                $this->type = self::FIELD_TYPE_SELECT;
                $this->options = is_array($options) ? $options : array(''=>'N/A');    //In case of bad constructor call, so we never feed an empty select
                break;
            case self::FIELD_TYPE_STATE:
                $this->type = self::FIELD_TYPE_STATE;
                $this->options = !empty($options) ? $options : self::getDefaultStateList();
                break;
            case self::FIELD_TYPE_COUNTRY:
                $this->type = self::FIELD_TYPE_COUNTRY;
                $this->options = !empty($options) ? $options : self::getDefaultCountryList();
                break;
            case self::FIELD_TYPE_HIDDEN:
                $this->type = self::FIELD_TYPE_HIDDEN;
                break;
            case self::FIELD_TYPE_CREDITCARD_NUMBER:
                $this->type = self::FIELD_TYPE_CREDITCARD_NUMBER;
            default:
                $this->type = self::FIELD_TYPE_STRING;    //Fall-back in case of bad constructor call
                break;
        }
        
        //See if there is an old session value
        if(CCDPostParserLP::openSession()){
            if(!empty($_SESSION['postparserData']['oldData'][$this->name])){
                //@TODO: This htmlspecialchars may end up in a look of multi-encoding. Or is there no chance of recursion since %gt; is valid?
                $this->oldValue = htmlspecialchars($_SESSION['postparserData']['oldData'][$this->name]);
            }
        }

        //Done
    }


    protected static function getDefaultStateList(){
        return $states = array(
        '' => 'Please Select a State *',
        'AL'=>'Alabama',
        'AK'=>'Alaska',
        'AZ'=>'Arizona',
        'AR'=>'Arkansas',
        'CA'=>'California',
        'CO'=>'Colorado',
        'CT'=>'Connecticut',
        'DE'=>'Delaware',
        'DC'=>'District of Columbia',
        'FL'=>'Florida',
        'GA'=>'Georgia',
        'HI'=>'Hawaii',
        'ID'=>'Idaho',
        'IL'=>'Illinois',
        'IN'=>'Indiana',
        'IA'=>'Iowa',
        'KS'=>'Kansas',
        'KY'=>'Kentucky',
        'LA'=>'Louisiana',
        'ME'=>'Maine',
        'MD'=>'Maryland',
        'MA'=>'Massachusetts',
        'MI'=>'Michigan',
        'MN'=>'Minnesota',
        'MS'=>'Mississippi',
        'MO'=>'Missouri',
        'MT'=>'Montana',
        'NE'=>'Nebraska',
        'NV'=>'Nevada',
        'NH'=>'New Hampshire',
        'NJ'=>'New Jersey',
        'NM'=>'New Mexico',
        'NY'=>'New York',
        'NC'=>'North Carolina',
        'ND'=>'North Dakota',
        'OH'=>'Ohio',
        'OK'=>'Oklahoma',
        'OR'=>'Oregon',
        'PA'=>'Pennsylvania',
        'RI'=>'Rhode Island',
        'SC'=>'South Carolina',
        'SD'=>'South Dakota',
        'TN'=>'Tennessee',
        'TX'=>'Texas',
        'UT'=>'Utah',
        'VT'=>'Vermont',
        'VA'=>'Virginia',
        'WA'=>'Washington',
        'WV'=>'West Virginia',
        'WI'=>'Wisconsin',
        'WY'=>'Wyoming',
       	'AE'=>'Armed Forces Africa \ Canada \ Europe \ Middle East',
		'AA'=>'Armed Forces America (Except Canada)',
		'AP'=>'Armed Forces Pacific'
         );
    }

    protected static function getDefaultCountryList(){
        return array(
        'Afghanistan' => 'Afghanistan',
		'Aland Islands' => 'Aland Islands',
		'Albania' => 'Albania',
		'Algeria' => 'Algeria',
		'American Samoa' => 'American Samoa',
		'Andorra' => 'Andorra',
		'Angola' => 'Angola',
		'Anguilla' => 'Anguilla',
		'Antarctica' => 'Antarctica',
		'Antigua And Barbuda' => 'Antigua And Barbuda',
		'Argentina' => 'Argentina',
		'Armenia' => 'Armenia',
		'Aruba' => 'Aruba',
		'Australia' => 'Australia',
		'Austria' => 'Austria',
		'Azerbaijan' => 'Azerbaijan',
		'Bahamas' => 'Bahamas',
		'Bahrain' => 'Bahrain',
		'Bangladesh' => 'Bangladesh',
		'Barbados' => 'Barbados',
		'Belarus' => 'Belarus',
		'Belgium' => 'Belgium',
		'Belize' => 'Belize',
		'Benin' => 'Benin',
		'Bermuda' => 'Bermuda',
		'Bhutan' => 'Bhutan',
		'Bolivia' => 'Bolivia',
		'Bosnia And Herzegovina' => 'Bosnia And Herzegovina',
		'Botswana' => 'Botswana',
		'Bouvet Island' => 'Bouvet Island',
		'Brazil' => 'Brazil',
		'British Indian Ocean Territory' => 'British Indian Ocean Territory',
		'Brunei Darussalam' => 'Brunei Darussalam',
		'Bulgaria' => 'Bulgaria',
		'Burkina Faso' => 'Burkina Faso',
		'Burundi' => 'Burundi',
		'Cambodia' => 'Cambodia',
		'Cameroon' => 'Cameroon',
		'Canada' => 'Canada',
		'Cape Verde' => 'Cape Verde',
		'Cayman Islands' => 'Cayman Islands',
		'Central African Republic' => 'Central African Republic',
		'Chad' => 'Chad',
		'Chile' => 'Chile',
		'China' => 'China',
		'Christmas Island' => 'Christmas Island',
		'Cocos (Keeling) Islands' => 'Cocos (Keeling) Islands',
		'Colombia' => 'Colombia',
		'Comoros' => 'Comoros',
		'Congo' => 'Congo',
		'Congo, Democratic Republic' => 'Congo, Democratic Republic',
		'Cook Islands' => 'Cook Islands',
		'Costa Rica' => 'Costa Rica',
		'Cote D\'Ivoire' => 'Cote D\'Ivoire',
		'Croatia' => 'Croatia',
		'Cuba' => 'Cuba',
		'Cyprus' => 'Cyprus',
		'Czech Republic' => 'Czech Republic',
		'Denmark' => 'Denmark',
		'Djibouti' => 'Djibouti',
		'Dominica' => 'Dominica',
		'Dominican Republic' => 'Dominican Republic',
		'Ecuador' => 'Ecuador',
		'Egypt' => 'Egypt',
		'El Salvador' => 'El Salvador',
		'Equatorial Guinea' => 'Equatorial Guinea',
		'Eritrea' => 'Eritrea',
		'Estonia' => 'Estonia',
		'Ethiopia' => 'Ethiopia',
		'Falkland Islands (Malvinas)' => 'Falkland Islands (Malvinas)',
		'Faroe Islands' => 'Faroe Islands',
		'Fiji' => 'Fiji',
		'Finland' => 'Finland',
		'France' => 'France',
		'French Guiana' => 'French Guiana',
		'French Polynesia' => 'French Polynesia',
		'French Southern Territories' => 'French Southern Territories',
		'Gabon' => 'Gabon',
		'Gambia' => 'Gambia',
		'Georgia' => 'Georgia',
		'Germany' => 'Germany',
		'Ghana' => 'Ghana',
		'Gibraltar' => 'Gibraltar',
		'Greece' => 'Greece',
		'Greenland' => 'Greenland',
		'Grenada' => 'Grenada',
		'Guadeloupe' => 'Guadeloupe',
		'Guam' => 'Guam',
		'Guatemala' => 'Guatemala',
		'Guernsey' => 'Guernsey',
		'Guinea' => 'Guinea',
		'Guinea-Bissau' => 'Guinea-Bissau',
		'Guyana' => 'Guyana',
		'Haiti' => 'Haiti',
		'Heard Island & Mcdonald Islands' => 'Heard Island & Mcdonald Islands',
		'Holy See (Vatican City State)' => 'Holy See (Vatican City State)',
		'Honduras' => 'Honduras',
		'Hong Kong' => 'Hong Kong',
		'Hungary' => 'Hungary',
		'Iceland' => 'Iceland',
		'India' => 'India',
		'Indonesia' => 'Indonesia',
		'Iran, Islamic Republic Of' => 'Iran, Islamic Republic Of',
		'Iraq' => 'Iraq',
		'Ireland' => 'Ireland',
		'Isle Of Man' => 'Isle Of Man',
		'Israel' => 'Israel',
		'Italy' => 'Italy',
		'Jamaica' => 'Jamaica',
		'Japan' => 'Japan',
		'Jersey' => 'Jersey',
		'Jordan' => 'Jordan',
		'Kazakhstan' => 'Kazakhstan',
		'Kenya' => 'Kenya',
		'Kiribati' => 'Kiribati',
		'Korea' => 'Korea',
		'Kuwait' => 'Kuwait',
		'Kyrgyzstan' => 'Kyrgyzstan',
		'Lao People\'s Democratic Republic' => 'Lao People\'s Democratic Republic',
		'Latvia' => 'Latvia',
		'Lebanon' => 'Lebanon',
		'Lesotho' => 'Lesotho',
		'Liberia' => 'Liberia',
		'Libyan Arab Jamahiriya' => 'Libyan Arab Jamahiriya',
		'Liechtenstein' => 'Liechtenstein',
		'Lithuania' => 'Lithuania',
		'Luxembourg' => 'Luxembourg',
		'Macao' => 'Macao',
		'Macedonia' => 'Macedonia',
		'Madagascar' => 'Madagascar',
		'Malawi' => 'Malawi',
		'Malaysia' => 'Malaysia',
		'Maldives' => 'Maldives',
		'Mali' => 'Mali',
		'Malta' => 'Malta',
		'Marshall Islands' => 'Marshall Islands',
		'Martinique' => 'Martinique',
		'Mauritania' => 'Mauritania',
		'Mauritius' => 'Mauritius',
		'Mayotte' => 'Mayotte',
		'Mexico' => 'Mexico',
		'Micronesia, Federated States Of' => 'Micronesia, Federated States Of',
		'Moldova' => 'Moldova',
		'Monaco' => 'Monaco',
		'Mongolia' => 'Mongolia',
		'Montenegro' => 'Montenegro',
		'Montserrat' => 'Montserrat',
		'Morocco' => 'Morocco',
		'Mozambique' => 'Mozambique',
		'Myanmar' => 'Myanmar',
		'Namibia' => 'Namibia',
		'Nauru' => 'Nauru',
		'Nepal' => 'Nepal',
		'Netherlands' => 'Netherlands',
		'Netherlands Antilles' => 'Netherlands Antilles',
		'New Caledonia' => 'New Caledonia',
		'New Zealand' => 'New Zealand',
		'Nicaragua' => 'Nicaragua',
		'Niger' => 'Niger',
		'Nigeria' => 'Nigeria',
		'Niue' => 'Niue',
		'Norfolk Island' => 'Norfolk Island',
		'Northern Mariana Islands' => 'Northern Mariana Islands',
		'Norway' => 'Norway',
		'Oman' => 'Oman',
		'Pakistan' => 'Pakistan',
		'Palau' => 'Palau',
		'Palestinian Territory, Occupied' => 'Palestinian Territory, Occupied',
		'Panama' => 'Panama',
		'Papua New Guinea' => 'Papua New Guinea',
		'Paraguay' => 'Paraguay',
		'Peru' => 'Peru',
		'Philippines' => 'Philippines',
		'Pitcairn' => 'Pitcairn',
		'Poland' => 'Poland',
		'Portugal' => 'Portugal',
		'Puerto Rico' => 'Puerto Rico',
		'Qatar' => 'Qatar',
		'Reunion' => 'Reunion',
		'Romania' => 'Romania',
		'Russian Federation' => 'Russian Federation',
		'Rwanda' => 'Rwanda',
		'Saint Barthelemy' => 'Saint Barthelemy',
		'Saint Helena' => 'Saint Helena',
		'Saint Kitts And Nevis' => 'Saint Kitts And Nevis',
		'Saint Lucia' => 'Saint Lucia',
		'Saint Martin' => 'Saint Martin',
		'Saint Pierre And Miquelon' => 'Saint Pierre And Miquelon',
		'Saint Vincent And Grenadines' => 'Saint Vincent And Grenadines',
		'Samoa' => 'Samoa',
		'San Marino' => 'San Marino',
		'Sao Tome And Principe' => 'Sao Tome And Principe',
		'Saudi Arabia' => 'Saudi Arabia',
		'Senegal' => 'Senegal',
		'Serbia' => 'Serbia',
		'Seychelles' => 'Seychelles',
		'Sierra Leone' => 'Sierra Leone',
		'Singapore' => 'Singapore',
		'Slovakia' => 'Slovakia',
		'Slovenia' => 'Slovenia',
		'Solomon Islands' => 'Solomon Islands',
		'Somalia' => 'Somalia',
		'South Africa' => 'South Africa',
		'South Georgia And Sandwich Isl.' => 'South Georgia And Sandwich Isl.',
		'Spain' => 'Spain',
		'Sri Lanka' => 'Sri Lanka',
		'Sudan' => 'Sudan',
		'Suriname' => 'Suriname',
		'Svalbard And Jan Mayen' => 'Svalbard And Jan Mayen',
		'Swaziland' => 'Swaziland',
		'Sweden' => 'Sweden',
		'Switzerland' => 'Switzerland',
		'Syrian Arab Republic' => 'Syrian Arab Republic',
		'Taiwan' => 'Taiwan',
		'Tajikistan' => 'Tajikistan',
		'Tanzania' => 'Tanzania',
		'Thailand' => 'Thailand',
		'Timor-Leste' => 'Timor-Leste',
		'Togo' => 'Togo',
		'Tokelau' => 'Tokelau',
		'Tonga' => 'Tonga',
		'Trinidad And Tobago' => 'Trinidad And Tobago',
		'Tunisia' => 'Tunisia',
		'Turkey' => 'Turkey',
		'Turkmenistan' => 'Turkmenistan',
		'Turks And Caicos Islands' => 'Turks And Caicos Islands',
		'Tuvalu' => 'Tuvalu',
		'Uganda' => 'Uganda',
		'Ukraine' => 'Ukraine',
		'United Arab Emirates' => 'United Arab Emirates',
		'United Kingdom' => 'United Kingdom',
		'United States' => 'United States',
		'United States Outlying Islands' => 'United States Outlying Islands',
		'Uruguay' => 'Uruguay',
		'Uzbekistan' => 'Uzbekistan',
		'Vanuatu' => 'Vanuatu',
		'Venezuela' => 'Venezuela',
		'Viet Nam' => 'Viet Nam',
		'Virgin Islands, British' => 'Virgin Islands, British',
		'Virgin Islands, U.S.' => 'Virgin Islands, U.S.',
		'Wallis And Futuna' => 'Wallis And Futuna',
		'Western Sahara' => 'Western Sahara',
		'Yemen' => 'Yemen',
		'Zambia' => 'Zambia',
		'Zimbabwe' => 'Zimbabwe'
		);
    }

}

class CCDPostParserFormFieldData extends CCDPostParserFormField{
    public $value = null;

    public static function buildFromFormField($value, CCDPostParserFormField $field){
        return new self($value, $field->name, $field->label, $field->type, $field->required);
    }

    public function __construct($value, $name, $label, $type, $required){
        parent::__construct($name, $label,  $type, $required);

        //Take care of casting values correctly
        switch($type){
            case self::FIELD_TYPE_INTEGER:
                $this->value = (int)$value;
                break;
            default:
                $this->value = (String)$value;
        }

        $this->value = $value;
    }
}