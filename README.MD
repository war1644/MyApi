# MyApi项目 
基于phalapi
[文档查看](http://docs.phalapi.net/#/v2.0/)

## 结构介绍
- Common: 工具类和框架的扩展，重写等。属于整体框架的代码都应放这里，方便跨项目调用
- config: 各模块的配置
- language: 语言列表
- public: 各模块入口文件
- src: 各模块的逻辑代码
- src/functions.php: 全局公共方法，不建议在此处写很多的方法，最好的方式应该是以静态类的方式写在Common下
- vendor: 框架核心，禁止改动！！！

## 调用链
`url -> pulic/xxx/index.php -> init.php -> config/xxx/*.php -> DependenceInjection -> PhalApi -> new {url的对应类,方法} -> output`

## 开发流程规范
* Api层 规范参数
* Domain层 写具体的业务逻辑
* Model层 写数据处理

## 其他



