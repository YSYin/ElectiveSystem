package main

import (
	"github.com/streadway/amqp"
	"log"
	"encoding/json"
	"fmt"
	"database/sql"
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


func processApplication(election CourseElection) {
	student_id := election.StudentID
	course_ids := election.CourseIDs

	db, err := sql.Open("mysql", "web_admin:WEB_ADMIN-2020@electivesystem@/web_elective_db?charset=utf8")
	failOnError(err, "Failed to Open database")

	stmt1, err := db.Prepare("SELECT course_id FROM student_course WHERE student_id = ? AND course_id = ?")
	failOnError(err, "Failed to Prepare sql")

	stmt2, err := db.Prepare("SELECT course_capacity, course_student_num FROM course WHERE course_id = ?")
	failOnError(err, "Failed to Prepare sql")

	stmt3, err := db.Prepare("UPDATE course SET course_student_num = ? WHERE course_id = ?")
	failOnError(err, "Failed to use sql")

	stmt4, err := db.Prepare("INSERT INTO student_course (student_id, course_id) VALUES (?, ?)")
	failOnError(err, "Failed to use sql")


	var message string

	for _, course_id := range course_ids {
		log.Printf("开始处理课程ID:%s\n", course_id)
		var course_temp int
		err = stmt1.QueryRow(student_id, course_id).Scan(&course_temp)
		if err != nil {
        	if err != sql.ErrNoRows {
            	failOnError(err, "Failed to use sql")
        	}
    	} else {
    		message = fmt.Sprintf("选课失败！学生ID:%s已选过课程ID:%s", student_id,course_id)
    		log.Println(message)
    		continue
    	}

		var course_capacity int
		var course_student_num int
		err = stmt2.QueryRow(course_id).Scan(&course_capacity, &course_student_num)
		if err != nil {
        	if err == sql.ErrNoRows {
        		message = fmt.Sprintf("选课失败！课程ID:%s不存在", course_id)
        		log.Println(message)
        		continue
        	} else {
        		failOnError(err, "Failed to use sql")
        	}
        }
        if (course_capacity <= course_student_num) {
        	message = fmt.Sprintf("选课失败！课程ID:%s选课人数已满", course_id)
        	log.Println(message)
        	continue
        }

        _, err := stmt3.Exec(1 + course_student_num, course_id)
		failOnError(err, "Failed to use sql")

		_, err = stmt4.Exec(student_id, course_id)
		failOnError(err, "Failed to use sql")

		message = fmt.Sprintf("选课成功！学生ID:%s已选到课程ID:%s", student_id, course_id)
		log.Println(message)
	}
	db.Close()
}

func recvFromQueue() {
	conn, err := amqp.Dial("amqp://web_admin:WEB_ADMIN-2020@electivesystem@localhost:5672/")
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

	msgs, err := ch.Consume(
		q.Name, // queue
		"",     // consumer
		true,   // auto-ack
		false,  // exclusive
		false,  // no-local
		false,  // no-wait
		nil,    // args
	)
	failOnError(err, "Failed to register a consumer")

	forever := make(chan bool)

	go func() {
		for d := range msgs {
			var election CourseElection
			if err := json.Unmarshal(d.Body, &election); err != nil {
				failOnError(err, "Failed to unmarshal JSON")
			}
			log.Println("开始处理......")
			processApplication(election)
			log.Println("处理结束......")
		}
	}()

	log.Println(" [*] Waiting for messages. To exit press CTRL+C")
	<-forever
}

func main() {
	recvFromQueue()
}