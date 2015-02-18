<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Http\JsonResponse;

class GetPointsRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/*
	 * {@inheritdoc}
	 */
	public function response(array $errors)
	{
		return new JsonResponse($errors, 422);
	}

	/*
	 * {@inheritdoc}
	 */
	public function forbiddenResponse()
	{
		return new JsonResponse(['error' => 'Forbidden'], 403);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'channel' 	=> 'min:2|max:25',
			'handle'	=> 'required|min:2|max:25'
		];
	}

}
