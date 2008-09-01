#!/usr/bin/env python

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

from socket import *                    # get socket constructor and constants
from praatwrapper import *

myHost = ''                             # server machine, '' means local host
myPort = 5555                          # listen on a non-reserved port number

sockobj = socket(AF_INET, SOCK_STREAM)       # make a TCP socket object
sockobj.bind((myHost, myPort))               # bind it to server port number 
sockobj.listen(5)                            # listen, allow 5 pending connects

while 1:                                     # listen until process killed
	connection, address = sockobj.accept()   # wait for next client connect
	print 'Server connected by', address     # connection is a new socket
	while 1:
		request = connection.recv(1024)         # read next line on client socket
		if not request: break                   # send a reply line to the client
		wrapper = praat()
		data = wrapper.query(request)
		print data
		connection.send(data)     # until eof when socket closed
	connection.close()
