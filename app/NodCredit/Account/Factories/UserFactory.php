<?php
namespace App\NodCredit\Account\Factories;

use App\NodCredit\Account\Exceptions\UserFactoryException;
use App\NodCredit\Account\User;
use App\Paystack\PaystackApi;
use \App\User as UserModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class UserFactory
{
    private $paystackApi;

    public static function createInvestor(array $data): User
    {
        $factory = app(static::class);

        // Validate data
        $factory->validate($data, [
            'email' => 'required|email|unique:users',
            'phone' => 'required|min:10|max:14|unique:users',
            'agree' => 'required|accepted',
        ]);

        $data = [
            'name' => array_get($data, 'name', ''),
            'phone' => array_get($data, 'phone'),
            'bvn' => array_get($data, 'bvn'),
            'bvn_phone' => array_get($data, 'bvn_phone'),
            'email' => array_get($data, 'email'),
            'dob'  => array_get($data, 'dob'),
            'password' => str_random(16),
            'force_change_pwd' => true,
            'role' => UserModel::ROLE_PARTNER
        ];

        $model = UserModel::create($data);

        return new User($model);
    }

    /**
     * @param array $data
     * @return User
     * @throws UserFactoryException
     */
    public static function createCustomer(array $data): User
    {
        $factory = app(static::class);

        // Validate data
        $factory->validate($data, [
            'bvn' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'required|min:10|max:14|unique:users',
            'agree' => 'required|accepted',
        ]);

        // Resolve BVN
        $bvnData = $factory->resolveBvn(array_get($data, 'bvn'));

        $data = [
            'name' => sprintf('%s %s', $bvnData->first_name, $bvnData->last_name),
            'phone' => array_get($data, 'phone'),
            'bvn' => array_get($data, 'bvn'),
            'bvn_phone' => $bvnData->mobile,
            'email' => array_get($data, 'email'),
            'dob'  => $bvnData->formatted_dob,
            'password' => str_random(16),
            'force_change_pwd' => true,
            'role' => UserModel::ROLE_USER
        ];

        $model = UserModel::create($data);

        return new User($model);
    }

    public function __construct(PaystackApi $paystackApi)
    {
        $this->paystackApi = $paystackApi;
    }

    private function validate(array $data, array $rules)
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->throwException($validator->errors());
        }

        return true;
    }

    private function resolveBvn(string $bvn): \stdClass
    {
        $errors = app(MessageBag::class);

        try {
//            $response = [
//                'status' => true,
//                'data' => (object) [
//                    "first_name" => "GRACE",
//                    "last_name" => "IGE",
//                    "dob" => "11-Jul-92",
//                    "formatted_dob" => "1992-07-11",
//                    "mobile" => "07085159451",
//                    "bvn" => "22327007339"
//                ]
//            ];
//
//            $response = (object) $response;
//
            $response = $this->paystackApi->resolveBvn($bvn);
        }
        catch (\Exception $exception) {
            $errors->add('bvn.required', 'Check your BVN number');

            $this->throwException($errors);
        }

        if (! $response->status) {
            $errors->add('bvn.required', 'Check your BVN number');

            $this->throwException($errors);
        }

        return $response->data;
    }

    private function throwException(MessageBag $errors)
    {
        throw new UserFactoryException('Validation errors', 0, null, $errors);
    }
}