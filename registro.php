<?php
class registro{

    public function geraPayload(){
              $jsonPayload= '{
            "nuCPFCNPJ":"123456789",
            "filialCPFCNPJ":"0001",
            "ctrlCPFCNPJ":"39",
            "cdTipoAcesso":"2",
            "clubBanco":"0",
            "cdTipoContrato":"0",
            "nuSequenciaContrato":"0",
            "idProduto":"09",
            "nuNegociacao":"123400000001234567",
            "cdBanco":"237",
            "eNuSequenciaContrato":"0",
            "tpRegistro":"1",
            "cdProduto":"0",
            "nuTitulo":"0",
            "nuCliente":"123456",
            "dtEmissaoTitulo":"25.05.2017",
            "dtVencimentoTitulo":"20.06.2017",
            "tpVencimento":"0",
            "vlNominalTitulo":"100",
            "cdEspecieTitulo":"04",
            "tpProtestoAutomaticoNegativacao":"0",
            "prazoProtestoAutomaticoNegativacao":"0",
            "controleParticipante":"",
            "cdPagamentoParcial":"",
            "qtdePagamentoParcial":"0",
            "percentualJuros":"0",
            "vlJuros":"0",
            "qtdeDiasJuros":"0",
            "percentualMulta":"0",
            "vlMulta":"0",
            "qtdeDiasMulta":"0",
            "percentualDesconto1":"0",
            "vlDesconto1":"0",
            "dataLimiteDesconto1":"",
            "percentualDesconto2":"0",
            "vlDesconto2":"0",
            "dataLimiteDesconto2":"",
            "percentualDesconto3":"0",
            "vlDesconto3":"0",
            "dataLimiteDesconto3":"",
            "prazoBonificacao":"0",
            "percentualBonificacao":"0",
            "vlBonificacao":"0",
            "dtLimiteBonificacao":"",
            "vlAbatimento":"0",
            "vlIOF":"0",
            "nomePagador":"Cliente Teste",
            "logradouroPagador":"rua Teste",
            "nuLogradouroPagador":"90",
            "complementoLogradouroPagador":"",
            "cepPagador":"12345",
            "complementoCepPagador":"500",
            "bairroPagador":"bairro Teste",
            "municipioPagador":"Teste",
            "ufPagador":"SP",
            "cdIndCpfcnpjPagador":"1",
            "nuCpfcnpjPagador":"12345648901234",
            "endEletronicoPagador":"",
            "nomeSacadorAvalista":"",
            "logradouroSacadorAvalista":"",
            "nuLogradouroSacadorAvalista":"0",
            "complementoLogradouroSacadorAvalista":"",
            "cepSacadorAvalista":"0",
            "complementoCepSacadorAvalista":"0",
            "bairroSacadorAvalista":"",
            "municipioSacadorAvalista":"",
            "ufSacadorAvalista":"",
            "cdIndCpfcnpjSacadorAvalista":"0",
            "nuCpfcnpjSacadorAvalista":"0",
            "endEletronicoSacadorAvalista":""
          }';
        $dados = json_encode(self::_codificaParamsUTF8($jsonPayload));
        $dados = $this->encryptBodyDataBradesco($dados);

        $sandboxUrl    = 'https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulohomologacao';
        $productionUrl = 'https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulo';
        
        $enviaBradesco = $this->executarRequisicao($sandboxUrl, $dados);


    }

    private function executarRequisicao($url, $dados, $headers = array()) {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if(count($headers) > 0){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);

        $resultado = curl_exec($ch);
        
        $info = curl_getinfo($ch);
        curl_close($ch);
             
        return array('statusHttp' => $info['http_code'], 'dados'=> $resultado);
    }


    private function encryptBodyDataBradesco($params)
    {
        ############ Dados Certificado ############
        $certificado = "Associacao_senha_associacao.pfx";
        $senhaCertificado = "associacao";
        $nomeDiretorio = "certificadoA1";
        ###########################################

        $message = json_encode($params);
        $folderPath = DIRETORIO . '/' . $nomeDiretorio . '/';

        $certFile = file_get_contents($folderPath . $certificado);

        # extrai do certificado a chave privada e certificado publico
        if (!openssl_pkcs12_read($certFile, $result, $senhaCertificado)) {
            return array("erro" => true, "msg" => "Não é possível ler o arquivo de certificado ({$certificado}). Por favor, verifique a senha do certificado.");
        }
        $certKey = openssl_x509_read($result['cert']);
        $privateKey = openssl_pkey_get_private($result['pkey'], $senhaCertificado);

        $msgFile = $folderPath . uniqid('__InputFile__', true);
        $signedFile = $folderPath . uniqid('__OutputFile__', true);

        file_put_contents($msgFile, $message);

        openssl_pkcs7_sign(
            $msgFile, $signedFile, $certKey, $privateKey, array(), PKCS7_BINARY | PKCS7_TEXT
        );

        $signature = file_get_contents($signedFile);
        $parts = preg_split("#\n\s*\n#Uis", $signature);
        $signedMessageBase64 = $parts[1];

        # remove arquivo temporario
        unlink($msgFile);
        unlink($signedFile);

        return $signedMessageBase64;

    }

}