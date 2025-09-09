package main

import (
	"net/http"

	"github.com/casbin/casbin/v2"
	gormadapter "github.com/casbin/gorm-adapter/v3"
	"github.com/gin-gonic/gin"
)

var enforcer *casbin.Enforcer

func main() {
	adapter, err := gormadapter.NewAdapter(
		"mysql",
		"root:@tcp(localhost)/blog", // adjust db creds
		true,
	)
	if err != nil {
		panic(err)
	}

	e, err := casbin.NewEnforcer("model.conf", adapter)
	if err != nil {
		panic(err)
	}
	if err := e.LoadPolicy(); err != nil {
		panic(err)
	}
	enforcer = e

	r := gin.Default()

	r.GET("/enforce", func(c *gin.Context) {
		sub := c.Query("sub")
		obj := c.Query("obj")
		act := c.Query("act")

		ok, _ := enforcer.Enforce(sub, obj, act)
		c.JSON(http.StatusOK, gin.H{"allowed": ok})
	})

	r.Run(":8080")
}
