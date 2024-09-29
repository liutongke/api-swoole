# -*- coding: utf-8 -*-
from flask import Flask, jsonify

from system_info import *

app = Flask(__name__)


@app.route('/')
def index():
    return "Welcome to System Info API! Use /system_info to get system data."


@app.route('/system_info')
def system_info():
    try:
        system_data = get_system_info()
        return jsonify(system_data)
    except Exception as e:
        return jsonify({"error": str(e)}), 500


def get_system_info():
    # 假设这些函数已经定义，并返回相应的信息
    system_info = GetSystemInfo()
    cpu_info = GetCpuInfo()
    cpu_constants = GetCpuConstants()
    mem_info = GetMemInfo()
    disk_info = GetDiskInfo()
    network_info = GetNetWork()  # 注意这里我假设原函数名有拼写错误，应该是 GetNetwork()
    load_average = GetLoadAverage()
    io_readwrite = GetIoReadWrite()
    system_version = GetSystemVersion()
    boot_time = GetBootTime()
    full_system_data = GetFullSystemData()

    # 将信息存储在字典中
    info_dict = {
        "系统信息": system_info,
        "CPU信息": cpu_info,
        "CPU常量信息": cpu_constants,
        "内存信息": mem_info,
        "磁盘信息": disk_info,
        "网络信息": network_info,
        "系统负载状态": load_average,
        "IO读写信息": io_readwrite,
        "操作系统版本": system_version,
        "系统启动时间": boot_time,
        "完整的系统信息": full_system_data,
    }

    return info_dict


if __name__ == '__main__':
    app.run(debug=True, port=12222, host='0.0.0.0')
