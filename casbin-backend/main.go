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

	// --- Policy Management ---

	r.GET("/policies", func(c *gin.Context) {
	policies, _ := enforcer.GetPolicy() // [][]string
	var result []gin.H

	for _, p := range policies {
		// Defensive: check length
		if len(p) >= 3 {
			result = append(result, gin.H{
				"sub": p[0],
				"obj": p[1],
				"act": p[2],
			})
		}
	}

	c.JSON(http.StatusOK, gin.H{"policies": result})
})

	r.POST("/policy", func(c *gin.Context) {
		var req struct {
			Sub string `json:"sub"`
			Obj string `json:"obj"`
			Act string `json:"act"`
		}
		if err := c.BindJSON(&req); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		added, err := enforcer.AddPolicy(req.Sub, req.Obj, req.Act)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		if added {
			_ = enforcer.SavePolicy()
			c.JSON(http.StatusOK, gin.H{"message": "Policy added"})
		} else {
			c.JSON(http.StatusConflict, gin.H{"message": "Policy already exists"})
		}
	})

	r.DELETE("/policy", func(c *gin.Context) {
		var req struct {
			Sub string `json:"sub"`
			Obj string `json:"obj"`
			Act string `json:"act"`
		}
		if err := c.BindJSON(&req); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		removed, err := enforcer.RemovePolicy(req.Sub, req.Obj, req.Act)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		if removed {
			_ = enforcer.SavePolicy()
			c.JSON(http.StatusOK, gin.H{"message": "Policy removed"})
		} else {
			c.JSON(http.StatusNotFound, gin.H{"message": "Policy not found"})
		}
	})

	// --- Role Management ---

	r.GET("/roles", func(c *gin.Context) {
	roles, _ := enforcer.GetGroupingPolicy() // [][]string
	var result []gin.H

	for _, rData := range roles {
		if len(rData) >= 2 {
			result = append(result, gin.H{
				"user": rData[0],
				"role": rData[1],
			})
		}
	}

	c.JSON(http.StatusOK, gin.H{"roles": result})
})

	r.POST("/role", func(c *gin.Context) {
		var req struct {
			User string `json:"user"`
			Role string `json:"role"`
		}
		if err := c.BindJSON(&req); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		added, err := enforcer.AddGroupingPolicy(req.User, req.Role)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		if added {
			_ = enforcer.SavePolicy()
			c.JSON(http.StatusOK, gin.H{"message": "Role assigned"})
		} else {
			c.JSON(http.StatusConflict, gin.H{"message": "Role already assigned"})
		}
	})

	r.DELETE("/role", func(c *gin.Context) {
		var req struct {
			User string `json:"user"`
			Role string `json:"role"`
		}
		if err := c.BindJSON(&req); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		removed, err := enforcer.RemoveGroupingPolicy(req.User, req.Role)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		if removed {
			_ = enforcer.SavePolicy()
			c.JSON(http.StatusOK, gin.H{"message": "Role removed"})
		} else {
			c.JSON(http.StatusNotFound, gin.H{"message": "Role not found"})
		}
	})


	r.Run(":8080")
	e.LoadPolicy()

}
