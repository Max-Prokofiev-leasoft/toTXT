using System.Collections;
using System.Collections.Generic;
using Unity.Mathematics;
using UnityEngine;
using static UnityEditor.Searcher.SearcherWindow.Alignment;

public class Enemy : MonoBehaviour
{

    private float horizontal;

    private bool isFacingRight = true;

    [SerializeField]
    private float _speed;

    [SerializeField]
    private float _rotationSpeed;

    private Rigidbody2D _rigidBody;
    private PlayerAwarenesOfController _playerAwarenesOfController;
    private Vector2 _targetDirection;

    private void Awake()
    {
        _rigidBody = GetComponent<Rigidbody2D>();
        _playerAwarenesOfController = GetComponent<PlayerAwarenesOfController>();
    }

    private void Update()
    {
        horizontal = Input.GetAxisRaw("Horizontal");
    }

    private void FixedUpdate()
    {
        UpdateTargetDirection();
        RotateTowardsTarget();
        SetVelocity();
    }

    private void UpdateTargetDirection()
    {
        if (_playerAwarenesOfController.AwareOfPlayer)
        {
            _targetDirection = _playerAwarenesOfController.DirectionToPlayer;
        }
        else
        {
            _targetDirection = Vector2.zero;
        }
    }

    private void RotateTowardsTarget()
    {
        if(_targetDirection == Vector2.zero)
        {
            return;
        }
        else
        {
            if (_targetDirection.x > 0 && !isFacingRight || _targetDirection.x < 0 && isFacingRight)
            {
                isFacingRight = !isFacingRight;
                transform.Rotate(0f, 180f, 0f);
            }
        }
    }

    private void SetVelocity()
    {
        if (_targetDirection == Vector2.zero)
        {
            _rigidBody.velocity = Vector2.zero;
        }
        else
        {
            _rigidBody.velocity = _targetDirection.normalized * _speed;
        }
    }

}
