using System.Collections;
using System.Collections.Generic;
using Unity.Mathematics;
using UnityEngine;
using UnityEngine.InputSystem;
using UnityEngine.UIElements;
using static UnityEditor.Searcher.SearcherWindow.Alignment;

public class PlayerMovement : MonoBehaviour
{
    [SerializeField]
    private float _speed;

    private float horizontal;

    private bool isFacingRight = true;

    private Rigidbody2D _rigidbody;
    private Vector2 _movementInput;
    private Vector2 _SmoothedMovementInput;
    private Vector2 _movementInputSmoothVelocity;
    private SpriteRenderer _spriteRenderer;
    private Animator _animator;
    
    private void Awake()
    {
        _rigidbody = GetComponent<Rigidbody2D>();
        _animator = GetComponent<Animator>();
    }

    private void FixedUpdate()
    {
        SetPlayerVelocity();
        Flip();
        SetAnimation();
    }

    private void SetAnimation()
    {
        bool isMoving = _movementInput != Vector2.zero;

        _animator.SetBool("IsMoving", isMoving);
    }

    private void Update()
    {
        horizontal = Input.GetAxisRaw("Horizontal");
    }

    private void SetPlayerVelocity()
    {
        _SmoothedMovementInput = Vector2.SmoothDamp(
            _SmoothedMovementInput,
            _movementInput,
            ref _movementInputSmoothVelocity,
            0.1f);
        _rigidbody.velocity = _SmoothedMovementInput * _speed;
    }

    private void Flip()
    {
        if (isFacingRight && horizontal < 0f || !isFacingRight && horizontal > 0f)
        {
            isFacingRight = !isFacingRight;
            transform.Rotate(0f, 180f, 0f);
        }
    }
    private void OnMove(InputValue InputValue)
    {
        _movementInput = InputValue.Get<Vector2>();
    }
}
