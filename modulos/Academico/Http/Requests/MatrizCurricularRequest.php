<?php

namespace Modulos\Academico\Http\Requests;

use Modulos\Core\Http\Request\BaseRequest;

class MatrizCurricularRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
            {
                return [
                    'mtc_crs_id' => 'required',
                    'mtc_file' => 'required|mimes:pdf',
                    'mtc_descricao' => 'required|max:255',
                    'mtc_data' => 'required|date_format:"d/m/Y"',
                    'mtc_creditos' => 'required',
                    'mtc_horas' => 'required',
                    'mtc_horas_praticas' => 'required'
                ];

            }
            case 'PATCH':
            case 'PUT':
            {
                return [
                    'mtc_crs_id' => 'required',
                    'mtc_descricao' => 'required|max:255',
                    'mtc_data' => 'required|date_format:"d/m/Y"',
                    'mtc_creditos' => 'required',
                    'mtc_horas' => 'required',
                    'mtc_horas_praticas' => 'required'
                ];
            }
            default: return [];
        }
    }
}
