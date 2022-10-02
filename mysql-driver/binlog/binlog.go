package binlog

import (
	"encoding/binary"
)

//https://dev.mysql.com/doc/internals/en/com-binlog-dump.html

func Binlog() []byte {
	//1              [12] COM_BINLOG_DUMP
	//4              binlog-pos
	//2              flags
	//4              server-id
	//string[EOF]    binlog-filename
	//head := make([]byte, 4) //头
	var b = make([]byte, 4)
	binary.LittleEndian.PutUint32(b, uint32(2344)) //binlog-pos
	var bytes = []byte{0x12}
	bytes = append(bytes, b...) //COM_BINLOG_DUMP

	bytes = append(bytes, 0x00, 0x00)

	var serverId = make([]byte, 4)
	binary.LittleEndian.PutUint32(serverId, uint32(5)) //server-id
	bytes = append(bytes, serverId...)

	bytes = append(bytes, []byte("mysql-bin.000001")...) //binlog-filename
	return bytes
}
