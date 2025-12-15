<?php
class AvaliacaoFisica {
    private $id;
    private $alunoId;
    private $dataAvaliacao;
    private $peso;
    private $altura;
    private $imc;
    private $percentualGordura;
    private $massaMuscular;
    private $circunferenciaBraco;
    private $circunferenciaCintura;
    private $circunferenciaQuadril;
    private $observacoes;
    private $proximaAvaliacao;
    private $dataCadastro;

    public function __construct($alunoId, $dataAvaliacao, $peso = null, $altura = null, $imc = null, $percentualGordura = null, $massaMuscular = null, $circunferenciaBraco = null, $circunferenciaCintura = null, $circunferenciaQuadril = null, $observacoes = null, $proximaAvaliacao = null, $id = null, $dataCadastro = null) {
        $this->id = $id;
        $this->alunoId = $alunoId;
        $this->dataAvaliacao = $dataAvaliacao;
        $this->peso = $peso;
        $this->altura = $altura;
        $this->imc = $imc;
        $this->percentualGordura = $percentualGordura;
        $this->massaMuscular = $massaMuscular;
        $this->circunferenciaBraco = $circunferenciaBraco;
        $this->circunferenciaCintura = $circunferenciaCintura;
        $this->circunferenciaQuadril = $circunferenciaQuadril;
        $this->observacoes = $observacoes;
        $this->proximaAvaliacao = $proximaAvaliacao;
        $this->dataCadastro = $dataCadastro;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getAlunoId() { return $this->alunoId; }
    public function getDataAvaliacao() { return $this->dataAvaliacao; }
    public function getPeso() { return $this->peso; }
    public function getAltura() { return $this->altura; }
    public function getImc() { return $this->imc; }
    public function getPercentualGordura() { return $this->percentualGordura; }
    public function getMassaMuscular() { return $this->massaMuscular; }
    public function getCircunferenciaBraco() { return $this->circunferenciaBraco; }
    public function getCircunferenciaCintura() { return $this->circunferenciaCintura; }
    public function getCircunferenciaQuadril() { return $this->circunferenciaQuadril; }
    public function getObservacoes() { return $this->observacoes; }
    public function getProximaAvaliacao() { return $this->proximaAvaliacao; }
    public function getDataCadastro() { return $this->dataCadastro; }

    // SETTERS
    public function setAlunoId($alunoId) { $this->alunoId = $alunoId; }
    public function setDataAvaliacao($dataAvaliacao) { $this->dataAvaliacao = $dataAvaliacao; }
    public function setPeso($peso) { $this->peso = $peso; }
    public function setAltura($altura) { $this->altura = $altura; }
    public function setImc($imc) { $this->imc = $imc; }
    public function setPercentualGordura($percentualGordura) { $this->percentualGordura = $percentualGordura; }
    public function setMassaMuscular($massaMuscular) { $this->massaMuscular = $massaMuscular; }
    public function setCircunferenciaBraco($circunferenciaBraco) { $this->circunferenciaBraco = $circunferenciaBraco; }
    public function setCircunferenciaCintura($circunferenciaCintura) { $this->circunferenciaCintura = $circunferenciaCintura; }
    public function setCircunferenciaQuadril($circunferenciaQuadril) { $this->circunferenciaQuadril = $circunferenciaQuadril; }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }
    public function setProximaAvaliacao($proximaAvaliacao) { $this->proximaAvaliacao = $proximaAvaliacao; }
}
?>

