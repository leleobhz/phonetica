# -*- coding: utf-8 -*-
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

import os
import sys
import re
import pexpect
import psyco

psyco.full()

class PraatError(Exception):
	def __init__(self,erro):
		self.erro = erro
	def __str___(self):
#		return repr(self.erro, Exception)
		return repr(self.erro)

class praat():

	def __init__(self):
		self._praat = pexpect.spawn ('praat -')
		self._praat.expect ('Praat > ')

	def __del__(self):
		self._praat.close()

	def query (self, command):
		self._praat.sendline (command)
		errorlevel = self._praat.expect ([command, 'Error:'])
		if errorlevel == 1:
			try:
				raise PraatError, str(self._praat.readline())
			except PraatError, e:
				print 'PraatError: \n\t', e.erro
		self._praat.expect ('\r\n')
		self._praat.expect ('Praat > ')
		return re.sub('\r\n$','',self._praat.before)

# END OF CODE
