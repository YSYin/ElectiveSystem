package main

import (
	"github.com/streadway/amqp"
	"log"
	"fmt"
	"strings"
	"net/http"
	"encoding/json"
	_ "github.com/go-sql-driver/mysql"
)

type CourseElection struct {
	StudentID         string
	CourseIDs       []string
}

func failOnError(err error, msg string) {
	if err != nil {
		log.Fatalf("%s: %s", msg, err)
	}
}

func sendToQueue(election_json []byte) {
	conn, err := amqp.Dial("amqp://root:Web-2020@localhost:5672/")
	failOnError(err, "Failed to connect to RabbitMQ")
	defer conn.Close()

	ch, err := conn.Channel()
	failOnError(err, "Failed to open a channel")
	defer ch.Close()

	q, err := ch.QueueDeclare(
		"election", // name
	  	false,   // durable
	  	false,   // delete when unused
	  	false,   // exclusive
	  	false,   // no-wait
	  	nil,     // arguments
	)
	failOnError(err, "Failed to declare a queue")

	err = ch.Publish(
		"",     // exchange
  		q.Name, // routing key
  		false,  // mandatory
  		false,  // immediate
  		amqp.Publishing {
  			ContentType: "application/json",
  			Body:        []byte(election_json),
  		})
	failOnError(err, "Failed to publish a message")
}

func process(w http.ResponseWriter, r *http.Request) {
	r.ParseForm() 
	if r.Method == "POST" {
		student_id := r.Form.Get("user_id")
		course_ids := strings.Split(r.Form.Get("course_ids"), ".")
		var election = CourseElection{student_id, course_ids}
		election_json, err := json.Marshal(election)
		log.Println("请求已发送")
		failOnError(err, "Failed to transform to JSON")
		sendToQueue([]byte(election_json))
		fmt.Fprintf(w, "您的选课请求已处理，请稍后点击左侧菜单查看选课结果")
	}
}

func main() {
	http.HandleFunc("/process", process) //设置访问的路由
	err := http.ListenAndServe(":9090", nil) //设置监听的端口
	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}