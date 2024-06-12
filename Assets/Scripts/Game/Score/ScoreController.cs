using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Events;

public class ScoreController : MonoBehaviour
{

    public UnityEvent onScoreChanged;
    public int score {  get; private set; }

    public void addScore(int amount)
    {
        score += amount;
        onScoreChanged.Invoke();
    }
}
