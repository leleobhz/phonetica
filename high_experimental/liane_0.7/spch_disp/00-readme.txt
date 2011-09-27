Instrucoes para reconfigurar o speech dispatcher com o sintetizador Liane

1. Matar o processo do speech-dispatcher se estiver rodando
(veja qual é o número dele com o comando ps ax)

2. Editar o arquivo /etc/speech-dispatcher/speechd.conf

Adicionar a seguinte linha antes do primeiro AddModule existente:
AddModule "lianetts"       "sd_generic"   "lianetts-generic.conf"

3. Mudar os seguintes parametros
DefaultVoiceType  "FEMALE1"
DefaultLanguage  "pt"
DefaultModule lianetts
LanguageDefaultModule "pt"  "lianetts"
LanguageDefaultModule "en"  "espeak"

4. Remover o diretorio .speech-dispatcher na raiz da conta atual
pois este arquivo é uma cópia da configuracao global do speech-dispatcher.

5. Testar o funcionamento do speech-dispatcher executando:
python spch-disp/liane-test.py
