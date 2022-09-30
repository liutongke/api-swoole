package server

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
