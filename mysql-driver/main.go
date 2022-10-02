package main

import (
	"go-mysql/binlog/binlog"
	"go-mysql/binlog/packet"
	"go-mysql/binlog/server"
)

//var sequenceId uint8 = 1 //包序列id

func main() {
	//binlog.Binlog()
	//return
	mysql := server.NewMysql("root", "root", "192.168.0.107", "3306")
	//mysql := server.NewMysql("root", "REMOVED", "192.168.0.105", "3304")
	//go PingTimer(Ping, mysql, 10*time.Second)

	authPacket := packet.NewHandshake().ReadAuthResult(mysql)
	mysql.Write(authPacket, 1) //发送auth Packet
	mysql.ShowMaster()
	mysql.SetChecksum()
	mysql.SetSlaveUuid()
	mysql.Show()
	mysql.RegisterSlave()
	mysql.Write(binlog.Binlog(), 0)
	for {
		packetData := mysql.Payload()
		//fmt.Println(packetData)
		packet.NewPacket().Handler(packetData, mysql)
		typeSql := server.UserInput()

		mysql.Query(typeSql)
	}
}
