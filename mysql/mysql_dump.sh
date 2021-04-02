#!/usr/bin/env bash

# 记得加执行权限
# 每天凌晨04:30执行一次
# 30 4 * * * /srun3/www/srun_mq/data/shell/mysql_dump.sh

# 配置
declare -A config=(
[path]=/home/mysql_backup # 备份保存位置
[host]=localhost
[username]=root # 数据库用户名
[password]=srun_3000 # 数据库密码
[database_name]=srun_mq # 要备份的库名 todo: 备份多个数据库
[mysqldump]=/srun3/mysql/bin/mysqldump # mysqldump 位置 可使用 `find / -name mysqldump` 进行查找
[structure]=30 # 表结构保留天数
[data]=10 # 数据保留天数
)

# 创建备份文件夹
path=${config[path]}
if [[ ! -d "${path}" ]]; then
    mkdir ${path}
    chmod -R 777 ${path}
fi

# 查询目录下文件数量
count_structure=`ls -l ${path}/structure_* | wc -l`
# 多于 (配置) 个则按创建时间删除多余文件
if [[ ${count_structure} -gt ${config[structure]} ]]; then
    stop=`expr ${count_structure} - ${config[structure]}`
    i=1
    for file in `ls -rt ${path}/structure_*` ; do
        if [[ ${i} -le ${stop} ]]; then
        echo "DELETE: "${file}
            rm -rf ${file}
        fi
        let "i++"
    done
fi
# 查询目录下文件数量
count_data=`ls -l ${path}/data_* | wc -l`
# 多于 (配置) 个则按创建时间删除多余文件
if [[ ${count_data} -gt ${config[data]} ]]; then
    stop=`expr ${count_data} - ${config[data]}`
    i=1
    for file in `ls -rt ${path}/data_*` ; do
        if [[ ${i} -le ${stop} ]]; then
            rm -rf ${file}
        fi
        let "i++"
    done
fi

# 备份 结构
`${config[mysqldump]} -u${config[username]} -p${config[password]} -h${config[host]} -d ${config[database_name]} > ${path}/structure_${config[database_name]}_$(date +%Y%m%d_%H%M%S).sql`
# 备份 结构 + 数据
#`${config[mysqldump]} -u${config[username]} -p${config[password]} ${config[database_name]} > ${path}/data_${config[database_name]}_$(date +%Y%m%d_%H%M%S).sql`
# 备份并压缩 结构 + 数据
`${config[mysqldump]} -u${config[username]} -p${config[password]} -h${config[host]} ${config[database_name]} | gzip > ${path}/data_${config[database_name]}_$(date +%Y%m%d_%H%M%S).sql.gz`
