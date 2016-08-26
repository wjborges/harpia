<?php

namespace Modulos\Geral\Repositories;

use Illuminate\Support\Facades\DB;
use Modulos\Core\Repository\BaseRepository;
use Modulos\Geral\Models\Pessoa;
use Stevebauman\EloquentTable\TableCollection;

class PessoaRepository extends BaseRepository
{
    public function __construct(Pessoa $pessoa)
    {
        $this->model = $pessoa;
    }

    public function paginate($sort = null, $search = null)
    {
        $result = $this->model->leftJoin('gra_documentos', function ($join) {
            $join->on('pes_id', '=', 'doc_pes_id')->on('doc_tpd_id', '=', 1, 'and', true);
        });

        if (!empty($search)) {
            foreach ($search as $value) {
                if ($value['field'] == 'pes_cpf') {
                    $result = $result->where('doc_conteudo', '=', $value['term']);
                    continue;
                }

                switch ($value['type']) {
                    case 'like':
                        $result = $result->where($value['field'], $value['type'], "%{$value['term']}%");
                        break;
                    default:
                        $result = $result->where($value['field'], $value['type'], $value['term']);
                }
            }
        }

        if (!empty($sort)) {
            $result = $result->orderBy($sort['field'], $sort['sort']);
        }

        $result = $result->paginate(15);

        return $result;
    }
}
