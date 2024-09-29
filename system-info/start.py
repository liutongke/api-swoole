#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os
import platform
import re
import subprocess


def get_os():
    os_name = platform.system()
    if os_name == "Windows":
        return True
    elif os_name == "Linux":
        return False


def windows():
    return get_wlan_ipv4_address()
    # 使用subprocess模块执行ipconfig命令，并使用findstr筛选包含"IPv4"的行
    # result = subprocess.check_output("ipconfig | findstr /i \"IPv4\"", shell=True)

    # 将输出结果按换行符分割成列表
    # lines = result.decode("gbk").split("\r\n")

    # 遍历列表，找到包含"IPv4"的行，并提取第16个字段
    # for line in lines:
    #     if "IPv4" in line:
    #         myip = line.split()[15]
    #         break
    # return myip


def get_wlan_ipv4_address():
    # 运行 ipconfig 命令并捕获输出
    ipconfig_output = subprocess.check_output(['ipconfig'], universal_newlines=True)

    # 使用正则表达式查找 WLAN 适配器的 IPv4 地址
    pattern = re.compile(r'WLAN(?:.*\n)+?.*IPv4 地址[.\s]+: ([^\s]+)')
    match = pattern.search(ipconfig_output)

    if match:
        # 返回匹配的 IPv4 地址
        return match.group(1)


def linux():
    # 获取本机IP
    ip = subprocess.check_output(["hostname", "-I"]).decode().strip().split()[0]
    return ip


# 获取当前目录
current_dir = os.getcwd()
data_dir = os.path.join(current_dir, 'log')

# 检查目录是否存在
if os.path.exists(data_dir):
    print(f"Directory already exists: {data_dir}")
else:
    # 创建目录
    os.mkdir(data_dir)
    print(f"Created directory: {data_dir}")

if get_os():
    myip = windows()
else:
    myip = linux()

print(f"本机的 IP 地址是：{myip}")

# 构建 Docker 镜像名称
image_name = 'system-info:v1'

# 构建 Docker 镜像
subprocess.run(['docker', 'build', '-f', './Dockerfile', '-t', image_name, "."], check=True)


def get_cpu_architecture():
    """
    获取当前系统的CPU架构信息
    树莓派 aarch64
    """
    if get_os():
        return "Windows"
    else:
        return platform.machine()


# 使用方法获取CPU架构信息
cpu_architecture = get_cpu_architecture()
print("CPU架构：", cpu_architecture)

if get_os():
    # 运行 Docker 容器
    subprocess.run(
        [
            'docker',
            'run',
            '--name', 'system-info-v1',
            '--restart=always',
            '-e', f'MY_IP={myip}',
            '-e', f'CPU_MODEL={cpu_architecture}',
            '-e', 'DOCKER_IN=1',
            '-e', 'DEBUG=1',
            '-it',
            '-p', '12222:12222',
            '-p', '12223:12223/udp',
            '-v', f'{current_dir}:/var/www/html',
            image_name, '/bin/bash'], check=True)
else:
    # 运行 Docker 容器
    subprocess.run(
        [
            'docker',
            'run',
            '--name', 'system-info-v1',
            '--restart=always',
            '-e', f'MY_IP={myip}',
            '-e', f'CPU_MODEL={cpu_architecture}',
            '-e', 'DOCKER_IN=1',
            '-e', 'DEBUG=1',
            '-it',
            '-p', '12222:12222',
            '-p', '12223:12223/udp',
            '-v', f'{current_dir}:/var/www/html',
            '-v', '/proc/cpuinfo:/proc/cpuinfo:ro',
            '-v', '/etc/os-release:/etc/os-release:ro',
            '-v', '/sys:/sys:ro',
            '-v', '/etc/passwd:/etc/passwd:ro',
            '-v', '/etc/group:/etc/group:ro',
            '-v', '/var/run/docker.sock:/var/run/docker.sock:ro',
            '--mount', 'type=tmpfs,destination=/dev/shm',
            '--privileged',
            image_name, '/bin/bash'
        ], check=True)
