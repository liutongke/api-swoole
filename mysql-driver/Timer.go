package main

import "time"

type fun func(interface{}) // 声明了一个函数类型
// 启动定时器进行心跳检测
func PingTimer(f fun, param interface{}, d time.Duration) {
	go func() {
		ticker := time.NewTicker(d)
		defer ticker.Stop()
		for {
			<-ticker.C //d执行一次
			//发送心跳
			f(param) //调用下函数
			//fmt.Println(fmt.Sprintf("%s 执行了一次定时任务", Timer.NowStr()))
		}
	}()
}
