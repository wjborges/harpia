<?php

namespace Modulos\Integracao\Listeners;

use Modulos\Academico\Models\Turma;
use Modulos\Academico\Repositories\CursoRepository;
use Modulos\Academico\Repositories\PeriodoLetivoRepository;
use Modulos\Academico\Repositories\TurmaRepository;
use Modulos\Integracao\Events\AtualizarSyncEvent;
use Modulos\Integracao\Events\TurmaMapeadaEvent;
use Modulos\Integracao\Repositories\AmbienteVirtualRepository;
use Modulos\Integracao\Repositories\SincronizacaoRepository;
use Moodle;

class MigrarTurmaListener
{
    private $turmaRepository;
    private $cursoRepository;
    private $periodoLetivoRepository;
    private $ambienteVirtualRepository;
    private $sincronizacaoRepository;

    public function __construct(TurmaRepository $turmaRepository,
                                CursoRepository $cursoRepository,
                                PeriodoLetivoRepository $periodoLetivoRepository,
                                AmbienteVirtualRepository $ambienteVirtualRepository,
                                SincronizacaoRepository $sincronizacaoRepository)
    {
        $this->turmaRepository = $turmaRepository;
        $this->cursoRepository = $cursoRepository;
        $this->periodoLetivoRepository = $periodoLetivoRepository;
        $this->ambienteVirtualRepository = $ambienteVirtualRepository;
        $this->sincronizacaoRepository = $sincronizacaoRepository;
    }

    public function handle(TurmaMapeadaEvent $event)
    {
        $turmasMigrar = $this->sincronizacaoRepository->findBy([
            'sym_table' => 'acd_turmas',
            'sym_status' => 1
        ]);

        if ($turmasMigrar->count()) {
            foreach ($turmasMigrar as $item) {
                $turma = $this->turmaRepository->find($item->sym_table_id);
                $ambiente = $this->ambienteVirtualRepository->getAmbienteByTurma($turma->trm_id);

                if (!$ambiente) {
                    continue;
                }

                $data['course']['trm_id'] = $turma->trm_id;
                $data['course']['category'] = 1;
                $data['course']['shortname'] = $this->turmaShortName($turma);
                $data['course']['fullname'] = $this->turmaFullName($turma);
                $data['course']['summaryformat'] = 1;
                $data['course']['format'] = 'topics';
                $data['course']['numsections'] = 0;


                $param['url'] = $ambiente->url;
                $param['token'] = $ambiente->token;
                $param['action'] = 'post';
                $param['functioname'] = 'local_integracao_create_course';
                $param['data'] = $data;

                $response = Moodle::send($param);
                $status = 3;

                if (array_key_exists('status', $response)) {
                    if ($response['status'] == 'success') {
                        $status = 2;
                    }
                }

                event(new AtualizarSyncEvent($turma, $status, $response['message']));
            }
        }
    }

    /**
     * @param Turma $turma
     * @return mixed|string
     */
    private function turmaShortName(Turma $turma)
    {
        $cursoId = $this->turmaRepository->getCurso($turma->trm_id);
        $curso = $this->cursoRepository->find($cursoId);
        $periodoLetivo = $this->periodoLetivoRepository->find($turma->trm_per_id);

        $shortName = $curso->crs_sigla .' ' . $turma->trm_nome . ' ' . $periodoLetivo->per_nome;
        $shortName = str_replace(' ', '_', $shortName);

        return $shortName;
    }

    /**
     * @param Turma $turma
     * @return string
     */
    private function turmaFullName(Turma $turma)
    {
        $cursoId = $this->turmaRepository->getCurso($turma->trm_id);
        $curso = $this->cursoRepository->find($cursoId);
        $periodoLetivo = $this->periodoLetivoRepository->find($turma->trm_per_id);

        $fullname = $curso->crs_nome .' - ' . $turma->trm_nome . ' - ' . $periodoLetivo->per_nome;

        return $fullname;
    }
}
