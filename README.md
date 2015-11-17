# swPromise，基于swoole的PHP promise框架

在日常的使用场景中，PHP一般用作接口聚合层。
一个业务请求可能会串行的请求多个接口A->B->C，此时如果接口B的响应时间较慢（关键性业务，需要有较长的timeout等待时间），则会导致请求整体的时间过长，严重降低系统的响应能力。
考虑到这个业务场景下，进程的主要时间用在等待网络io返回。
如果能够使用异步编程的方式，则会极大的提升服务的吞吐量（NodeJS的优势）。

如果某接口响应时间超过往常，会导致php-fpm进程数急剧升高，从而导致大量cpu资源浪费在进程调度上面，甚至导致服务崩溃。swPromise框架是为了解决该问题而开发的。


异步非阻塞模式是实现高性能网络编程的一种方法。
传统上，为进行异步调用，会在代码中实现大量的回调函数，导致代码可读性与可维护性的急剧下降。
为了解决这个问题，主流方案有以下几种：

- 自定义事件式方案
- Promise/Deferred
- 高阶函数篡改回调函数
- 协程(Generator)

[Swoole](http://www.swoole.com/)是PHP语言的高性能网络通信框架，提供了PHP语言的异步多线程服务器。
swoole采用自定义事件式方案，为我们提供网络层基本封装。基于swoole，可以扩展出业务层的异步开发框架。

[tsf （Tencent Server Framework）](https://github.com/tencent-php) 是腾讯公司推出的 PHP 协程方案，基于 Swoole+PHP Generator 实现的 Coroutine。
该框架使用协程模式，基于swoole与swoole framework开发。
实现了真正的异步非阻塞开发模式，同时具有极高的性能。
其核心代码来源于该文章[Cooperative multitasking using coroutines (in PHP!) ](http://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html)。
tsf使用了较为复杂的用户态任务调度逻辑，暂时没有看到生产环境的使用案例。另外由于使用了swoole framework，也使其略显重量级。


swPromise的主要处理流程在Core\Async\Promise类中。
该类实现了基本的then方法，并通过对promise流程的延迟计算，保证了异步流程的动态控制能力。
该框架是一个非常基础的web框架，目前仅实现通用Future（通用延迟计算）、HttpClientFuture、ResponseFuture三个延迟计算类。

该框架需要配合[Swoole](https://github.com/swoole/swoole-src)、[php-http-parser](https://github.com/coooold/php_http_parser)扩展使用，第二个扩展用于解析http协议。

## 演示代码

```php
class Handler_Index extends \Core\Handler{
	public function run($request, $response){
		Promise::create ( Model::getUserInfo ( 'user1', 'haha' ) )
			->then (function(&$promise){
				$user1 = $promise->get('user1');
				if($user1){
					return Model::getUserInfo ( 'user2', 'haha2' )
							->then(function(&$promise){
								$user2 = $promise->get('user2');
								$promise->accept(['user3'=>$user2['body']]);
							});
				}
				else $promise->accept();
			})
			->then ( Model::getUserInfo ( 'user4', 'haha4' ) )
			->then ( Model::getUserInfo ( 'user5', 'haha5' ) )
			->then ( new ResponseFuture ($response) )
			->start ( new PromiseContext () );
	}
}
```

这段流程表明了，先获取haha这个用户的信息，写入上下文的user1字段中。
如果获取到了数据，再获取haha2这个用户的信息，写入上下文user2字段中。
并将user2的body字段放入user3字段中。然后获取haha4和haha5的信息。
最后将所有数据输出到网页。

可以看到，在第一个then中，通过if条件返回promise对象，实现了对异步流程的动态控制。
同样的，整个流程通过then串联起来，已经较为接近同步代码的书写了。
而使用回调的方式，代码会变得极为恐怖。

## 存在问题
目前没有实现when/any方法，因此无法实现多个promise并行发出。
其中Handler_Sync实现的就是该框架同步的使用方式。
另外，目前reject方法以及异常处理流程均没有实现，有兴趣的朋友可以自行扩展。

目前有一个比较严重的bug，如果大量http request没有完成就自行中断的话，会导致swoole http server发生错误，从而退出。在swoole前面放一个nginx就可以解决问题。

## 测试方法
启动

	php run.php
	
测试：

	ab -n 10000 -c 100 "http://localhost:9502/async"
	ab -n 10000 -c 100 "http://localhost:9502/sync"

经过测试，在后端接口响应性能有问题的情况下，swPromise可以同时处理大量连接，用很低的cpu负载等待接口数据返回。

---

引用资料：
 - [Swoole](http://www.swoole.com/)
 - [tsf （Tencent Server Framework）](https://github.com/tencent-php)
 - [Generator与异步编程](http://www.infoq.com/cn/articles/generator-and-asynchronous-programming/)
 - [Node.js回调黑洞全解：Async、Promise 和 Generato](http://zhuanlan.zhihu.com/FrontendMagazine/19750470)










