package server

import (
	"encoding/hex"
	"strings"
)

func GetOrder(idx string) uint8 {
	m := map[string]uint8{
		"exit":            01,
		"quit":            01,
		"use":             03,
		"select":          03,
		"insert":          03,
		"delete":          03,
		"update":          03,
		"show":            03,
		"create database": 05,
		"create table":    99,
	}

	return m[strings.ToLower(idx)]
}

// SET @master_heartbeat_period= 30000001024
// SET @master_binlog_checksum= @@global.binlog_checksum
// SET @slave_uuid= '62c16f3c-41ab-11ed-8d8e-0242ac110002'
//163	5.714582	192.168.0.105	192.168.0.107	MySQL	76	Request Register Slave

func (conn *Mysql) ShowMaster() {
	var bytes = []byte{0x03}
	conn.Write(append(bytes, []byte("show master status;")...), 0)
}
func (conn *Mysql) Show() {
	var bytes = []byte{0x03}
	conn.Write(append(bytes, []byte("show global variables like 'binlog_checksum';")...), 0)
}

func (conn *Mysql) SetSlaveUuid() {
	var bytes = []byte{0x03}
	conn.Write(append(bytes, []byte("SET @slave_uuid= '6ac0fdae-b5d7-11e4-a9f3-0800278ce5c9';")...), 0)
}

func (conn *Mysql) SetChecksum() {
	var bytes = []byte{0x03}
	conn.Write(append(bytes, []byte("set @master_binlog_checksum= @@global.binlog_checksum;")...), 0)
}

func (conn *Mysql) RegisterSlave() {
	var bytes = []byte{0x15}
	decodeString, err := hex.DecodeString("02000000000000ea0c0000000000000000")
	if err != nil {
		return
	}
	conn.Write(append(bytes, decodeString...), 0)
}

func (conn *Mysql) SetChart() []byte {
	var bytes = []byte{0x03}
	conn.Write(append(bytes, []byte("SET NAMES utf8;")...), 0)
	return nil
}

func (conn *Mysql) Query(typeSql *sql) []byte {
	var bytes = []byte{typeSql.head}
	conn.Write(append(bytes, []byte(typeSql.Sql)...), 0)
	return nil
}
