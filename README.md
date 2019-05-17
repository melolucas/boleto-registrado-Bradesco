## Aplicação que Boleto Registrado do banco Bradesco

Eu montei esse pequeno script com o intuído de ajudar aqueles que com eu, acham a documentação do banco uma porcaria.<br>
Recentemente tive que demanda de boleto registrado, e para minha frustação a documentação não me ajudou muito para assinar a requisição no padrão PKCS#7<br><br>
Por elaborei o código de forma mais simples possível e sem o uso de qualquer framework , para facilitar a visualização dos passos.

#### Trecho do Código que e o principal para a assinatura da requisição
```php
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

```

<br>
Ressalvo que isso e somente um exemplo simples com o intuito de ajudar aqueles que estão com problemas similares.



<br>
O certificado usado é um certificado de teste , podendo ser utilizando para teste incial, lembrando que o mesmo não sera processado no banco, retornando a mensagem
<br>
```html
Erro Certificado / Formatacao dos campos da mensagem invalida [0x00d30003] (810)
```
<br>
Mas pode ficar tranquilo, essa mensagem e o retorno do Bradesco, ou seja sua aplicação chegou corretamente no banco e foi descriptografada corretamente.