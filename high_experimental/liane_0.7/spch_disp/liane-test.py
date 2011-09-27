# -*- coding: utf-8 -*-
import speechd
client = speechd.SSIPClient('test')
client.set_output_module('espeak')
client.set_language('pt')
client.speak("Testando o espeak, agora mudando para liane.")
client.set_output_module('lianetts')
client.speak("Testando a Liane tts.")
client.speak("Você gosta de pêssego?")
client.close()

